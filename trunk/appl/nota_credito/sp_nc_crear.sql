ALTER PROCEDURE [dbo].[sp_nc_crear] 
(
	@ve_cod_factura				numeric, 
	@ve_cod_usuario				numeric,
	@cod_tipo_nc_interno_sii	numeric
)
AS
BEGIN  

		declare @vl_cod_empresa			numeric
			,@vl_cod_persona			numeric
			,@vl_referencia				varchar(100) 
			,@vl_cod_sucursal_factura	numeric
			,@ve_subtotal				numeric 
			,@ve_porc_dscto1			numeric 
			,@ve_porc_dscto2			numeric   
			,@ve_total_neto				numeric 
			,@ve_porc_iva				numeric 
			,@ve_monto_iva				numeric  
			,@ve_total_con_iva			numeric
			,@cod_motivo_nc				numeric
			,@cod_tipo_nota_credito		numeric

	select @vl_cod_empresa = cod_empresa
	,@vl_cod_persona = cod_persona
	,@vl_referencia = referencia
	,@vl_cod_sucursal_factura = cod_sucursal_factura
	,@ve_subtotal=subtotal
	,@ve_porc_dscto1=porc_dscto1
	,@ve_porc_dscto2=porc_dscto2
	,@ve_total_neto=total_neto
	,@ve_porc_iva=porc_iva
	,@ve_monto_iva=monto_iva 
	,@ve_total_con_iva=total_con_iva 
	from factura where cod_factura = @ve_cod_factura
		
	if(@cod_tipo_nc_interno_sii = 1)BEGIN
		set @cod_motivo_nc = 1
		set @cod_tipo_nota_credito = 1
	END	
	else if(@cod_tipo_nc_interno_sii = 2)BEGIN	
		set @cod_motivo_nc = 99
		set @cod_tipo_nota_credito = 1
	END
	else if(@cod_tipo_nc_interno_sii = 3)BEGIN	
		set @cod_motivo_nc = 1
		set @cod_tipo_nota_credito = 2	
	END
	else if(@cod_tipo_nc_interno_sii = 4)BEGIN	
		set @cod_motivo_nc = 98
		set @cod_tipo_nota_credito = 2	
	END
	else if(@cod_tipo_nc_interno_sii = 5)BEGIN
		set @cod_motivo_nc = 98
		set @cod_tipo_nota_credito = 3	
	END
		
	execute spu_nota_credito 
	'INSERT' 					-- ve_operacion
	,NULL 						-- ve_cod_nota_credito = identity
	,NULL 						-- cod_usuario_impresion
	,@ve_cod_usuario 
	,NULL 						-- ve_nro_nota_credito
	,NULL						--FECHA_NOTA_CREDITO	
	,1 							-- cod_estado_doc_sii = emitida
	,@vl_cod_empresa 
	,@vl_cod_sucursal_factura	-- ve_cod_sucursal_factura*
	,@vl_cod_persona
	,@vl_referencia 
	,NULL 						-- obs
	,NULL 						-- cod_bodega
	,@cod_tipo_nota_credito		-- cod_tipo_nota_credito = factura
	,@ve_cod_factura 
	,@ve_subtotal			
	,@ve_total_neto		
	,@ve_porc_dscto1
	,@ve_porc_dscto2		
	,'P' --ingreso_usuario_dscto1 	
	,0 --monto_dscto1		
	,'P' --ingreso_usuario_dscto2 	
	,0 --monto_dscto2		
	,@ve_porc_iva			
	,@ve_monto_iva			 
	,@ve_total_con_iva	
	,NULL 						-- motivo_anula
	,NULL 						-- cod_usuario_anula 
	,@cod_motivo_nc -- cod_motivo_nc
	,NULL	--genera_entrada
	,@cod_tipo_nc_interno_sii	--cod_tipo_nc_interno_sii
	,NULL
	,NULL
	,NULL
	,'CREAR_DESDE'
END
