CREATE PROCEDURE [dbo].[spx_carga_gd]
AS
BEGIN  
	declare @nro_gd				numeric
			,@fecha_guia_despacho varchar(10)
			,@count				numeric
			,@rut				numeric
			,@dig_verif			varchar(1)
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
			,@estado			varchar(10)
			,@cod_estado_nota_venta numeric
			,@fecha_anula		varchar(10)
			,@motivo_anula		varchar(100)
			,@cod_usuario_anula	numeric
			,@tipo_gd			numeric
			,@cod_tipo_guia_despacho numeric
			,@numero_nota_venta	numeric
			,@cod_doc			numeric
			,@count_nv			numeric

		
	declare c_aux_gd cursor for 
	select numero_guia_despacho
			,fecha_guia_despacho
			,convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1)) --rut
			,substring(RUT_CLIENTE, len(RUT_CLIENTE), 1) --dig_verif
			,estado
			,tipo_gd
			,numero_nota_venta
	from aux_guia_despacho

	open c_aux_gd 
	fetch c_aux_gd into @nro_gd, @fecha_guia_despacho, @rut, @dig_verif, @estado, @tipo_gd, @numero_nota_venta
	WHILE @@FETCH_STATUS = 0 BEGIN
		select @count=count(*) from guia_despacho where nro_guia_despacho = @nro_gd
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
			set @cod_estado_nota_venta = null
			set @cod_tipo_guia_despacho = null
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
				--los datos de la empresa los obtiene por la NV de la GD
				-- en 4D no había integridad de datos
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
				set @cod_estado_nota_venta = 2
				set @fecha_anula = null
				set @motivo_anula = null
				set @cod_usuario_anula = null
			end
			else if (@estado ='Anulada')
			begin
				set @cod_estado_nota_venta = 4
				select @fecha_anula	= @fecha_guia_despacho
					,@motivo_anula = 'ANULADO EN SISTEMA 4D'
					,@cod_usuario_anula = 1
			end

			-- obtiene el cod_tipo_guia_despacho
			if (@tipo_gd = 0) -- en 4D = normal
				set @cod_tipo_guia_despacho = 1 -- venta
			else if (@tipo_gd = 1) -- en 4D = demo
				set @cod_tipo_guia_despacho = 2 -- demo
			--else if (@tipo_gd = 2) -- en 4D = arriendo
				-- no hay tipo_gd = 2
			else if (@tipo_gd = 3) -- en 4D = garantia
				set @cod_tipo_guia_despacho = 3 --garantia


			insert into GUIA_DESPACHO
			   (FECHA_REGISTRO
			   ,COD_USUARIO
			   ,NRO_GUIA_DESPACHO
			   ,FECHA_GUIA_DESPACHO
			   ,COD_ESTADO_DOC_SII
			   ,COD_EMPRESA
			   ,COD_SUCURSAL_DESPACHO
			   ,COD_PERSONA
			   ,REFERENCIA
			   ,NRO_ORDEN_COMPRA
			   ,OBS
			   ,RETIRADO_POR
			   ,RUT_RETIRADO_POR
			   ,DIG_VERIF_RETIRADO_POR
			   ,GUIA_TRANSPORTE
			   ,PATENTE
			   ,COD_FACTURA
			   ,GENERA_SALIDA
			   ,COD_BODEGA
			   ,COD_TIPO_GUIA_DESPACHO
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
			   ,COD_USUARIO_IMPRESION)
			select getdate()	--FECHA_REGISTRO
				,1				--COD_USUARIO
				,@nro_gd		--NRO_GUIA_DESPACHO
				,@fecha_guia_despacho
				,@cod_estado_nota_venta 	
				,@cod_empresa
				,@cod_sucursal
				,@cod_persona
				,REFERENCIA
				,ORDEN_COMPRA
				,OBSERVACIONES
				,RETIRADO_POR
				,convert(numeric, substring(RUT, 1, len(RUT) - 1))  --RUT_RETIRADO_POR
				,substring(RUT, len(RUT), 1)						--DIG_VERIF_RETIRADO_POR
				,GUIA_TRANS
				,PATENTE
				,null				--COD_FACTURA, estan todos los campos en null
				,'N'				--GENERA_SALIDA, estan todos con 'N'
				,null				--COD_BODEGA, estan todos los campos en null
				,@cod_tipo_guia_despacho --COD_TIPO_GUIA_DESPACHO
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
				,1 --COD_USUARIO_IMPRESION
			from aux_guia_despacho
			where numero_guia_despacho = @nro_gd
			
			--marca aux_guia_despacho, cuando se traspasa la GD
			update aux_guia_despacho
			set cod_guia_despacho = @@identity
			where numero_guia_despacho = @nro_gd
		end
		fetch c_aux_gd into @nro_gd, @fecha_guia_despacho, @rut, @dig_verif, @estado, @tipo_gd, @numero_nota_venta
	end
	close c_aux_gd
	deallocate c_aux_gd
END
go