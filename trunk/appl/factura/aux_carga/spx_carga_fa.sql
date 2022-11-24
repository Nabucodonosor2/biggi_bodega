CREATE PROCEDURE [dbo].[spx_carga_fa]
AS
BEGIN  
	declare @nro_fa				numeric
			,@fecha				varchar(10)
			,@count				numeric
			,@rut				numeric
			,@dig_verif			varchar(1)
			,@estado			varchar(10)
			,@codigo_vendedor	varchar(4)
			,@codigo_vendedor2	varchar(4)
			,@usuario_anula		varchar(4)
			,@condiciones_venta	varchar(100)
			,@cod_forma_pago	numeric
			,@numero_nota_venta numeric
			,@cod_doc			numeric
			,@fecha_anula_4d	varchar(10)
			,@emisor			varchar(30)
			,@cod_empresa		numeric
			,@nom_empresa		varchar(100)
			,@giro				varchar(100)
			,@cod_sucursal		numeric
			,@nom_sucursal		varchar(100)
			,@direccion			varchar(100)
			,@cod_comuna		numeric
			,@cod_ciudad		numeric
			,@cod_pais			numeric
			,@telefono			varchar(100)
			,@fax				varchar(100)
			,@cod_persona		numeric
			,@nom_persona		varchar(100)
			,@email				varchar(100)
			,@cod_cargo			numeric
			,@cod_estado_doc_sii numeric
			,@fecha_anula		varchar(10)
			,@motivo_anula		varchar(100)
			,@cod_usuario_anula	numeric
			,@count_nv			numeric
			,@cod_usuario_impresion numeric
			,@cod_usuario_vendedor1 numeric
			,@cod_usuario_vendedor2 numeric
	
	declare c_aux_fa cursor for 
	select numero_factura
			,fecha
			,convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1)) --rut
			,substring(RUT_CLIENTE, len(RUT_CLIENTE), 1) --dig_verif
			,estado
			,codigo_vendedor
			,codigo_vendedor2
			,condiciones_venta
			,numero_nota_venta
			,fecha_anula
			,usuario_anula
			,emisor
	from aux_factura
	where estado <> 'Anulada' --hay 85 registros con estado anulado: con rut 19, numero_nota_venta = 0, por lo tanto no hay como obtener el dato del cliente

	open c_aux_fa 
	fetch c_aux_fa into @nro_fa, @fecha, @rut, @dig_verif, @estado, @codigo_vendedor, @codigo_vendedor2, @condiciones_venta, @numero_nota_venta, @fecha_anula_4d, @usuario_anula, @emisor
	WHILE @@FETCH_STATUS = 0 BEGIN
		select @count=count(*) from factura where nro_factura = @nro_fa
		if (@count = 0) begin
			-- obtiene datos de empresa, sucursal y persona
			set @cod_empresa = null
			set @nom_empresa = null
			set @giro = null
			set @cod_sucursal = null
			set @nom_sucursal = null
			set @direccion = null
			set @cod_comuna = null
			set @cod_ciudad = null
			set @cod_pais = null
			set @telefono = null
			set @fax = null
			set @cod_persona = null
			set @nom_persona = null
			set @email = null
			set @cod_cargo = null
			set @cod_estado_doc_sii = null
			set @count_nv = 0

			-- obtiene el cod_doc
			if (@numero_nota_venta = 0)
			begin
				set @cod_doc = null
				select @cod_empresa = cod_empresa
					,@nom_empresa = nom_empresa
					,@giro = giro
				from empresa where rut = @rut
			end
			else
			begin
				--los datos de la empresa los obtiene por la NV de la fa
				-- en 4D no hay integridad de datos
				set @cod_doc = @numero_nota_venta
				select @count_nv = count(*) from nota_venta where cod_nota_venta = @numero_nota_venta
				if(@count_nv = 0)--no existe la NV en el nuevo sistema
					select @cod_empresa = cod_empresa
						,@nom_empresa = nom_empresa
						,@giro = giro
					from empresa where rut = @rut
				else
					select @cod_empresa = e.cod_empresa
						,@nom_empresa = e.nom_empresa
						,@giro = e.giro
					from empresa e, nota_venta nv
					where nv.cod_nota_venta = @numero_nota_venta
						and e.cod_empresa = nv.cod_empresa
			end

			select top 1 @cod_sucursal = cod_sucursal 
						,@nom_sucursal = nom_sucursal
						,@direccion = direccion
						,@cod_comuna = cod_comuna
						,@cod_ciudad = cod_ciudad
						,@cod_pais = cod_pais
						,@telefono = telefono
						,@fax = fax
			from sucursal where cod_empresa = @cod_empresa
			select top 1 @cod_persona = cod_persona
						,@nom_persona = nom_persona
						,@email = email
						,@cod_cargo = cod_cargo 
			from persona where cod_sucursal = @cod_sucursal 

			--obtiene datos de anulacion
			if (@estado ='Impresa')
			begin
				set @cod_estado_doc_sii = 2
				set @fecha_anula = null
				set @motivo_anula = null
				set @cod_usuario_anula = null
			end
			else if (@estado ='Anulada')
			begin
				set @cod_estado_doc_sii = 4
				set @fecha_anula	= @fecha_anula_4d
				set	@motivo_anula = 'ANULADO EN SISTEMA 4D'


				if (@usuario_anula='D' or @usuario_anula='D2' or @usuario_anula is null)
					set @cod_usuario_anula = 1
				else
					select @cod_usuario_anula = cod_usuario from usuario where ini_usuario=@usuario_anula
			end
			
			set @cod_usuario_impresion = null
			select @cod_usuario_impresion = cod_usuario from usuario where nom_usuario=@emisor
			if (@cod_usuario_impresion is null)
				set @cod_usuario_impresion = 1
			
			-- obtiene los cod_usuario de los vendedores
			set @cod_usuario_vendedor1 = null
			set @cod_usuario_vendedor2 = null
			if (@codigo_vendedor is null)
				set @cod_usuario_vendedor1 = null
			else if (@codigo_vendedor='D' or @codigo_vendedor='D2')
				set @cod_usuario_vendedor1 = 1
			else
				select @cod_usuario_vendedor1 = cod_usuario from usuario where ini_usuario=@codigo_vendedor
				
			if (@codigo_vendedor2 is null)
				set @cod_usuario_vendedor2 = null
			else if (@codigo_vendedor2='D' or @codigo_vendedor2='D2')
				set @cod_usuario_vendedor2 = 1
			else
				select @cod_usuario_vendedor2 = cod_usuario from usuario where ini_usuario=@codigo_vendedor2

			-- obtiene COD_FORMA_PAGO
			set @cod_forma_pago = 1	-- otro
			if (@condiciones_venta='50 % OC - 50 % CE' or @condiciones_venta='50% OC - SALDO CONTRA ENTREGA' or @condiciones_venta='50% OC- 50% CE' or @condiciones_venta='50% OC -50% CE' or @condiciones_venta='50% OC-50% CE')
				set @cod_forma_pago = 4	
			else if (@condiciones_venta='50 % OC - saldo a 30 dias' or @condiciones_venta='50% OC - SALDO 30 DIAS' or @condiciones_venta='50% OC-SALDO 30 DIAS')
				set @cod_forma_pago = 8
			else if (@condiciones_venta='50% ANT. SALDO 30 Y 60 DIAS' or @condiciones_venta='50% OC - SALDO A 30 Y 60 DIAS' or @condiciones_venta='AL DIA, 30 Y 60 DIAS')
				set @cod_forma_pago = 9
			else if (@condiciones_venta='AL DÆA')
				set @cod_forma_pago = 11
			else if (@condiciones_venta='CCE')
				set @cod_forma_pago = 5
			else if (@condiciones_venta='CCF - 30 DIAS' or @condiciones_venta='CCF-30 dias' or @condiciones_venta='CHEQUE 30 DÆAS' or @condiciones_venta='CHEQUE AL DÆA Y 30 DÆAS' or @condiciones_venta='CONTRA FACTURA 30' or @condiciones_venta='CONTRA FACTURA 30 DÆAS')
				set @cod_forma_pago = 7
			else if (@condiciones_venta='CCF - 60 Dias' or @condiciones_venta='CONTRA FACTURA 60 DÆAS')
				set @cod_forma_pago = 10
			else if (@condiciones_venta='CHEQUE AL DÆA' or @condiciones_venta='CHEQUE AL DIA')
				set @cod_forma_pago = 3
			else if (@condiciones_venta='CONTADO' or @condiciones_venta='CONTADO CONTRA FACTURA' or @condiciones_venta='CONTRA FACTURA')
				set @cod_forma_pago = 6
			else if (@condiciones_venta='EFECTIVO')
				set @cod_forma_pago = 2
			else if (@condiciones_venta='ORDEN DE COMPRA, CCE - 30 DÆAS')
				set @cod_forma_pago = 13
			else if (@condiciones_venta='TARJETA DE CREDITO')
				set @cod_forma_pago = 14
			
			insert into FACTURA
			   (FECHA_REGISTRO
			   ,COD_USUARIO
			   ,NRO_FACTURA
			   ,FECHA_FACTURA
			   ,COD_ESTADO_DOC_SII
			   ,COD_EMPRESA
			   ,COD_SUCURSAL_FACTURA
			   ,COD_PERSONA
			   ,REFERENCIA
			   ,NRO_ORDEN_COMPRA
			   ,OBS
			   ,RETIRADO_POR
			   ,RUT_RETIRADO_POR
			   ,DIG_VERIF_RETIRADO_POR
			   ,GUIA_TRANSPORTE
			   ,PATENTE
			   ,GENERA_SALIDA
			   ,COD_BODEGA
			   ,COD_TIPO_FACTURA
			   ,COD_DOC
			   ,FECHA_ANULA
			   ,MOTIVO_ANULA
			   ,COD_USUARIO_ANULA
			   ,RUT
			   ,DIG_VERIF
			   ,NOM_EMPRESA
			   ,GIRO
			   ,NOM_SUCURSAL
			   ,DIRECCION
			   ,COD_COMUNA
			   ,COD_CIUDAD
			   ,COD_PAIS
			   ,TELEFONO
			   ,FAX
			   ,NOM_PERSONA
			   ,MAIL
			   ,COD_CARGO
			   ,COD_USUARIO_IMPRESION
			   ,COD_USUARIO_VENDEDOR1
			   ,PORC_VENDEDOR1
			   ,COD_USUARIO_VENDEDOR2
			   ,PORC_VENDEDOR2
			   ,COD_FORMA_PAGO
			   ,COD_ORIGEN_VENTA
			   ,SUBTOTAL
			   ,PORC_DSCTO1
			   ,INGRESO_USUARIO_DSCTO1
			   ,MONTO_DSCTO1
			   ,PORC_DSCTO2
			   ,INGRESO_USUARIO_DSCTO2
			   ,MONTO_DSCTO2
			   ,TOTAL_NETO
			   ,PORC_IVA
			   ,MONTO_IVA
			   ,TOTAL_CON_IVA
			   ,PORC_FACTURA_PARCIAL
			   ,NOM_FORMA_PAGO_OTRO
			   ,TIPO_DOC)
			select getdate()	--FECHA_REGISTRO
				,1				--COD_USUARIO
				,@nro_fa		--NRO_FACTURA
				,@fecha
				,@cod_estado_doc_sii
				,@cod_empresa
				,@cod_sucursal
				,@cod_persona
				,REFERENCIA
				,ORDEN_COMPRA
				,OBSERVACION
				,RETIRADO_POR
				,convert(numeric, substring(RUT_RETIRADO, 1, len(RUT_RETIRADO) - 1))  --RUT_RETIRADO_POR
				,substring(RUT_RETIRADO, len(RUT_RETIRADO), 1)						--DIG_VERIF_RETIRADO_POR
				,GUIA_TRANSPORTE	
				,PATENTE
				,case REBAJA_POR_DESP when 'VERDADERO' then 
					'S'
				else
					'N'
				end				--GENERA_SALIDA
				,NULL			--COD_BODEGA
				,1				--COD_TIPO_FACTURA, 1 = VENTA
				,@cod_doc
				,@fecha_anula				
				,@motivo_anula
				,@cod_usuario_anula
				,@rut 
				,@dig_verif
				,@nom_empresa
				,@giro
				,@nom_sucursal
				,@direccion
				,@cod_comuna
				,@cod_ciudad
				,@cod_pais
				,@telefono
				,@fax
				,@nom_persona
				,@email
				,@cod_cargo
				,@cod_usuario_impresion
				,@cod_usuario_vendedor1
				,PORCENTAJE_COMISION_V1
				,@cod_usuario_vendedor2
				,case PORCENTAJE_COMISION_V2 when 0.00 then 
					NULL
				else
					PORCENTAJE_COMISION_V2
				end
				,@cod_forma_pago			
				,case ES_VENTA_SV when 'VERDADERO' then 
					1
				else
					NULL
				end
				,TOTAL			--SUBTOTAL
				,PORCE			--PORC_DSCTO1
				,'M'			--INGRESO_USUARIO_DSCTO2
				,TOTAL_DESC1	--MONTO_DSCTO1
				,PORCENTAJE_DESC2 --PORC_DSCTO2
				,'M'			--INGRESO_USUARIO_DSCTO2
				,TOTAL_DESC2	--MONTO_DSCTO2
				,TOTAL_NETO
				,IVA
				,TOTAL_IVA
				,TOTAL_CON_IVA
				,NULL			--PORC_FACTURA_PARCIAL, todas las facturas estan con NULL
				,NULL			--NOM_FORMA_PAGO_OTRO
				,'NOTA_VENTA'	--TIPO_DOC, se asume que todo viene desde nota de venta
			from aux_factura
			where numero_factura = @nro_fa
			
			--marca aux_factura, cuando se traspasa la FA
			update aux_factura
			set cod_factura = @@identity
			where numero_factura = @nro_fa
		end
		fetch c_aux_fa into @nro_fa, @fecha, @rut, @dig_verif, @estado, @codigo_vendedor, @codigo_vendedor2, @condiciones_venta, @numero_nota_venta, @fecha_anula_4d, @usuario_anula, @emisor
	end
	close c_aux_fa
	deallocate c_aux_fa
END
go