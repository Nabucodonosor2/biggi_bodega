ALTER PROCEDURE [dbo].[sp_fa_crear] 
(
	@ve_cod_nota_venta numeric, 
	@ve_cod_usuario numeric
)
AS
BEGIN  
	
	declare @vl_cod_empresa				numeric
			,@vl_cod_sucursal_despacho	numeric
			,@vl_cod_persona			numeric
			,@vl_referencia				varchar(100) 
			,@vl_nro_orden_compra		varchar(40)
			,@vl_fecha_orden_compra_cliente			datetime			
			,@vl_fecha_factura			varchar(10)
			,@vl_cod_sucursal_factura	numeric
			,@ve_cod_usuario_vendedor1	numeric 
			,@ve_porc_vendedor1			T_PORCENTAJE 
			,@ve_cod_usuario_vendedor2	numeric 
			,@ve_porc_vendedor2			T_PORCENTAJE 
			,@ve_cod_forma_pago			numeric 
			,@ve_cod_origen_venta		numeric 
			,@ve_subtotal				T_PRECIO 
			,@ve_porc_dscto1			T_PORCENTAJE
			,@ve_porc_dscto2			T_PORCENTAJE 
			,@ve_total_neto				T_PRECIO 
			,@ve_porc_iva				T_PORCENTAJE
			,@ve_monto_iva				T_PRECIO  
			,@ve_nom_forma_pago_otro	varchar(100)
			,@ve_total_con_iva			T_PRECIO

	select @vl_cod_empresa = cod_empresa
	,@vl_cod_sucursal_despacho = cod_sucursal_despacho
	,@vl_cod_persona = cod_persona
	,@vl_referencia = referencia
	,@vl_nro_orden_compra = nro_orden_compra 
	,@vl_fecha_orden_compra_cliente = fecha_orden_compra_cliente
	,@vl_cod_sucursal_factura = cod_sucursal_factura
	,@ve_cod_usuario_vendedor1=cod_usuario_vendedor1
	,@ve_porc_vendedor1=porc_vendedor1
	,@ve_cod_usuario_vendedor2=cod_usuario_vendedor2
	,@ve_porc_vendedor2=porc_vendedor2
	,@ve_cod_forma_pago=cod_forma_pago
	,@ve_cod_origen_venta=cod_origen_venta
	,@ve_subtotal=subtotal
	,@ve_porc_dscto1=porc_dscto1
	,@ve_porc_dscto2=porc_dscto2
	,@ve_total_neto=total_neto
	,@ve_porc_iva=porc_iva
	,@ve_monto_iva=monto_iva 
	,@ve_total_con_iva=total_con_iva 
	,@ve_nom_forma_pago_otro=nom_forma_pago_otro
	from nota_venta where cod_nota_venta = @ve_cod_nota_venta
	


	
		
	execute spu_factura 
	'INSERT' 					-- ve_operacion
	,NULL 						-- ve_cod_factura = identity
	,NULL 						-- cod_usuario_impresion
	,@ve_cod_usuario 
	,NULL 						-- ve_nro_factura
	,NULL						-- FECHA_FACTURA	
	,1 							-- cod_estado_doc_sii = emitida
	,@vl_cod_empresa 
	,@vl_cod_sucursal_factura	-- ve_cod_sucursal_factura*
	,@vl_cod_persona
	,@vl_referencia 
	,@vl_nro_orden_compra
	,@vl_fecha_orden_compra_cliente
	,NULL 						-- obs
	,NULL 						-- retirado_por
	,NULL 						-- rut_retirado_por
	,NULL 						-- dig_verif_retirado_por
	,NULL 						-- guia_transporte
	,NULL 						-- patente
	,NULL 						-- cod_bodega
	,1 							-- cod_tipo_factura = venta
	,@ve_cod_nota_venta 
	,NULL 						-- motivo_anula
	,NULL 						-- cod_usuario_anula 
	,@ve_cod_usuario_vendedor1	
	,@ve_porc_vendedor1			
	,@ve_cod_usuario_vendedor2	
	,@ve_porc_vendedor2		
	,@ve_cod_forma_pago		
	,@ve_cod_origen_venta		
	,@ve_subtotal			
	,@ve_porc_dscto1		
	,'P' 	
	,0 -- monto_dscto1		
	,@ve_porc_dscto2		
	,'P' 	
	,0 -- monto_dscto2		
	,@ve_total_neto			
	,@ve_porc_iva			
	,@ve_monto_iva			 
	,@ve_total_con_iva		
	,NULL					--@ve_porc_factura_parcial
	,@ve_nom_forma_pago_otro
	,'S'
	,'NOTA_VENTA'	
	,'N'	--CANCELADA
END
go

