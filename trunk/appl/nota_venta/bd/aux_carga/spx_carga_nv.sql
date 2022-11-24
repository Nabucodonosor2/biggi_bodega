alter PROCEDURE spx_carga_nv
AS
BEGIN  
	declare @nro_nv		numeric
			,@count			numeric
			,@rut			numeric
			,@dig_verif		varchar(1)
			,@cod_vendedor1	numeric
			,@cod_vendedor2	numeric
			,@vendedor1		varchar(10)
			,@vendedor2		varchar(10)
			,@cod_empresa	numeric
			,@cod_usuario_cierre numeric	
			,@usuario_cierre	varchar(10)
			,@cod_usuario_anula	numeric
			,@usuario_anula		varchar(10)
			,@cod_sucursal		numeric
			,@cod_estado_nota_venta		numeric
			,@ANULACION_CONFIRMADA		varchar(10)
			,@cerrada					varchar(10)
			,@cod_persona				numeric
			,@cod_origen_venta			numeric
			,@SALA_VENTA				varchar(10)
			,@FORMA_PAGO				varchar(100)
			,@cod_forma_pago			numeric	
		

	declare c_aux_nv cursor for 
	select numero_nota_venta
			,convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1)) rut
			,vendedor1
			,vendedor2
			,usuario_cierre
			,USUARIO_ANULA_CONFIRMADA
			,ANULACION_CONFIRMADA
			,cerrada
			,SALA_VENTA
			,FORMA_PAGO
	from aux_nota_venta
	--where numero_nota_venta=50669

	open c_aux_nv 
	fetch c_aux_nv into @nro_nv,@rut,@vendedor1,@vendedor2,@usuario_cierre,@usuario_anula,@ANULACION_CONFIRMADA,@cerrada,@SALA_VENTA,@FORMA_PAGO
	WHILE @@FETCH_STATUS = 0 BEGIN
		select @count=count(*) from nota_venta where cod_nota_venta = @nro_nv
		if (@count = 0) begin
			-- obtiene los cod_usuario de los vendedores
			set @cod_vendedor1 = null
			set @cod_vendedor2 = null
			if (@vendedor1 is null)
				set @cod_vendedor1 = null
			else if (@vendedor1='D' or @vendedor1='D2')
				set @cod_vendedor1 = 1
			else
				select @cod_vendedor1 = cod_usuario from usuario where ini_usuario=@vendedor1
				
			if (@vendedor2 is null)
				set @cod_vendedor2 = null
			else if (@vendedor2='D' or @vendedor2='D2')
				set @cod_vendedor2 = 1
			else
				select @cod_vendedor2 = cod_usuario from usuario where ini_usuario=@vendedor2
	
			-- obtiene el cod_empresa
			set @cod_empresa = null
			set @cod_sucursal = null
			set @cod_persona = null
			select @cod_empresa = cod_empresa from empresa where rut = @rut
			select top 1 @cod_sucursal = cod_sucursal from sucursal where cod_empresa = @cod_empresa
			select top 1 @cod_persona = cod_persona from persona where cod_sucursal = @cod_sucursal

			-- obtiene COD_USUARIO_CIERRE
			set @cod_usuario_cierre= 1
			select @cod_usuario_cierre = cod_usuario from usuario where ini_usuario=@usuario_cierre
			
			--obtiene el  @cod_usuario_anula
			set @cod_usuario_anula = null
			select @cod_usuario_cierre = cod_usuario from usuario where ini_usuario=@usuario_anula

			-- obtiene el @cod_estado_nota_venta
			if (@ANULACION_CONFIRMADA ='VERDADERO')
				set @cod_estado_nota_venta = 3	-- anulada
			else if (@cerrada='VERDADERO')
				set @cod_estado_nota_venta = 2	-- cerrada
			else
				set @cod_estado_nota_venta = 4	-- confirmada, se asume confirmada
			
			-- obtiene cod_origen_venta
			set @cod_origen_venta = null
			if (@SALA_VENTA='VERDADERO')
				set @cod_origen_venta = 1

			-- obtiene COD_FORMA_PAGO
			set @cod_forma_pago = 1	-- otro
			if (@FORMA_PAGO='CCE')
				set @cod_forma_pago = 5	--CONTADO CONTRA ENTREGA
			else if (@FORMA_PAGO='CCF-30 dias')
				set @cod_forma_pago = 7	--CCF - 30 DIAS
			else if (@FORMA_PAGO='CHEQUE AL DIA')
				set @cod_forma_pago = 3	--CHEQUE AL DÍA
			else if (@FORMA_PAGO='TARJETA DE CREDITO')
				set @cod_forma_pago = 14	--TARJETA DE CREDITO
			else
				select @cod_forma_pago = cod_forma_pago from forma_pago where nom_forma_pago=@FORMA_PAGO
		
			-- crea la NV
			insert into nota_venta
				(COD_NOTA_VENTA
				,FECHA_NOTA_VENTA
				,FECHA_REGISTRO
				,COD_USUARIO
				,COD_ESTADO_NOTA_VENTA
				,NRO_ORDEN_COMPRA
				,CENTRO_COSTO_CLIENTE
				,COD_MONEDA
				,VALOR_TIPO_CAMBIO
				,COD_COTIZACION
				,COD_USUARIO_VENDEDOR1
				,PORC_VENDEDOR1
				,COD_USUARIO_VENDEDOR2
				,PORC_VENDEDOR2
				,COD_CUENTA_CORRIENTE
				,COD_ORIGEN_VENTA
				,REFERENCIA
				,COD_EMPRESA
				,COD_SUCURSAL_DESPACHO
				,COD_SUCURSAL_FACTURA
				,COD_PERSONA
				,FECHA_PLAZO_CIERRE
				,SUBTOTAL
				,PORC_DSCTO1
				,MONTO_DSCTO1
				,INGRESO_USUARIO_DSCTO1
				,PORC_DSCTO2
				,MONTO_DSCTO2
				,INGRESO_USUARIO_DSCTO2
				,TOTAL_NETO
				,PORC_IVA
				,MONTO_IVA
				,TOTAL_CON_IVA
				,FECHA_ENTREGA
				,OBS_DESPACHO
				,OBS
				,COD_FORMA_PAGO
				,FECHA_ANULA
				,MOTIVO_ANULA
				,COD_USUARIO_ANULA
				,NOM_FORMA_PAGO_OTRO
				,CANTIDAD_DOC_FORMA_PAGO_OTRO
				,PORC_DSCTO_CORPORATIVO
				,PORC_DSCTO_CORPORATIVO2
				,FECHA_CIERRE
				,COD_USUARIO_CIERRE
				,FECHA_CONFIRMA
				,COD_USUARIO_CONFIRMA)
			select @nro_nv					--COD_NOTA_VENTA
				,FECHA_NOTA_VENTA			--FECHA_NOTA_VENTA
				,getdate()					--FECHA_REGISTRO
				,1							--COD_USUARIO
				,@cod_estado_nota_venta		--COD_ESTADO_NOTA_VENTA
				,ORDEN_COMPRA				--NRO_ORDEN_COMPRA
				,NRO_CENTRO_COSTO			--CENTRO_COSTO_CLIENTE
				,1							--COD_MONEDA
				,1							--VALOR_TIPO_CAMBIO
				,null						--COD_COTIZACION (OJO se asume null porque no se estan cargando las COTIZACIONES)
				,@cod_vendedor1				--COD_USUARIO_VENDEDOR1
				,COMISION_V1				--PORC_VENDEDOR1
				,@cod_vendedor2				--COD_USUARIO_VENDEDOR2
				,COMISION_V2				--PORC_VENDEDOR2
				,1--COD_CUENTA_CORRIENTE = **********se debe traducir desde COD_CTA_CTE
				,@cod_origen_venta			-- COD_ORIGEN_VENTA
				,REFERENCIA					--REFERENCIA
				,@cod_empresa				--COD_EMPRESA
				,@cod_sucursal				--COD_SUCURSAL_DESPACHO
				,@cod_sucursal				--COD_SUCURSAL_FACTURA
				,@cod_persona				--COD_PERSONA
				,isnull(PLAZO_CIERRE_NV, convert(varchar, dateadd(dd, 10,FECHA_NOTA_VENTA), 103)) --FECHA_PLAZO_CIERRE
				,TOTAL						--SUBTOTAL
				,DESC1						--PORC_DSCTO1
				,TOTAL_DESC1				--MONTO_DSCTO1
				,'M'						--INGRESO_USUARIO_DSCTO1
				,DESC2						--PORC_DSCTO2
				,TOTAL_DESC2				--MONTO_DSCTO2
				,'M'						--INGRESO_USUARIO_DSCTO2
				,TOTAL_NETO					--TOTAL_NETO
				,IVA						--PORC_IVA
				,TOTAL_IVA					--MONTO_IVA
				,TOTAL_CON_IVA				--TOTAL_CON_IVA
				,isnull(FECHA_ENTREGA, convert(varchar, getdate(), 103)) --FECHA_ENTREGA, si viene vacio se asume getdate
				,DESPACHO					--OBS_DESPACHO
				,OBSERVACIONES				--OBS
				,@cod_forma_pago			--COD_FORMA_PAGO
				,FECHA_ANULA_CONFIRMADA		--FECHA_ANULA
				,substring(MOTIVO_ANULA_SOLICITADA, 1, 100)	--MOTIVO_ANULA
				,@cod_usuario_anula			--COD_USUARIO_ANULA
				,null						--NOM_FORMA_PAGO_OTRO
				,null						--CANTIDAD_DOC_FORMA_PAGO_OTRO
				,DCTO_NO_DECLARADO			--PORC_DSCTO_CORPORATIVO
				,0							--PORC_DSCTO_CORPORATIVO2
				,FECHA_CIERRE				--FECHA_CIERRE
				,@cod_usuario_cierre		--COD_USUARIO_CIERRE
				,getdate()					--FECHA_CONFIRMA
				,1							--COD_USUARIO_CONFIRMA
			from aux_nota_venta
			where numero_nota_venta = @nro_nv
		end
/*		
caso 1:
   *** EN_DEMO                   VARCHAR(10)                   ,
   **** EN_GARANTIA               VARCHAR(10)                   ,

select  numero_nota_venta,   EN_GARANTIA
from aux_nota_venta
where EN_DEMO <> 'FALSO'

select  numero_nota_venta,   EN_GARANTIA
from aux_nota_venta
where EN_GARANTIA <> 'FALSO'

se deben anular a mano

2.- completar a mano
    ***COD_CTA_CTE               NUMERIC(10)                   ,
*/
		fetch c_aux_nv into @nro_nv,@rut,@vendedor1,@vendedor2,@usuario_cierre,@usuario_anula,@ANULACION_CONFIRMADA,@cerrada,@SALA_VENTA,@FORMA_PAGO
	end
	close c_aux_nv
	deallocate c_aux_nv
END