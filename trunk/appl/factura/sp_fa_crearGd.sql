CREATE PROCEDURE [dbo].[sp_fa_crearGd]
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
			,@vl_fecha_orden_compra_cliente		datetime
			,@vl_cod_sucursal_factura	numeric
			,@vl_cod_usuario_vendedor1	numeric 
			,@vl_porc_vendedor1			T_PORCENTAJE 
			,@vl_cod_usuario_vendedor2	numeric 
			,@vl_porc_vendedor2			T_PORCENTAJE 
			,@vl_cod_forma_pago			numeric 
			,@vl_cod_origen_venta		numeric 
			,@vl_subtotal				T_PRECIO
			,@vl_total_neto				T_PRECIO 
			,@vl_porc_iva				T_PORCENTAJE 
			,@vl_monto_iva				T_PRECIO  
			,@vl_nom_forma_pago_otro	varchar(100)
			,@vl_total_con_iva			T_PRECIO
			,@vl_porc_dscto_nv			numeric(10,2)

	select @vl_cod_empresa = nv.cod_empresa
	,@vl_cod_sucursal_despacho = nv.cod_sucursal_despacho
	,@vl_cod_persona = nv.cod_persona
	,@vl_referencia = nv.referencia
	,@vl_nro_orden_compra = nv.nro_orden_compra 
	,@vl_fecha_orden_compra_cliente = nv.fecha_orden_compra_cliente 
	,@vl_cod_sucursal_factura = nv.cod_sucursal_factura
	,@vl_cod_usuario_vendedor1 = nv.cod_usuario_vendedor1
	,@vl_porc_vendedor1 = nv.porc_vendedor1
	,@vl_cod_usuario_vendedor2 = nv.cod_usuario_vendedor2
	,@vl_porc_vendedor2 = nv.porc_vendedor2
	,@vl_cod_forma_pago = nv.cod_forma_pago
	,@vl_cod_origen_venta = nv.cod_origen_venta
	,@vl_subtotal = nv.subtotal
	,@vl_porc_dscto_nv = round(((nv.subtotal - nv.total_neto) / nv.subtotal) * 100, 1) 
	,@vl_total_neto = nv.total_neto
	,@vl_porc_iva = nv.porc_iva
	,@vl_monto_iva= nv.monto_iva 
	,@vl_total_con_iva = nv.total_con_iva 
	,@vl_nom_forma_pago_otro= nv.nom_forma_pago_otro
	from nota_venta nv
	where nv.cod_nota_venta=@ve_cod_nota_venta
		
	execute spu_factura 
	'INSERT' 					-- ve_operacion
	,NULL 						-- ve_cod_factura = identity
	,NULL 						-- cod_usuario_impresion
	,@ve_cod_usuario 
	,NULL 						-- ve_nro_factura	
	,NULL						--FECHA_FACTURA	
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
	,@vl_cod_usuario_vendedor1	
	,@vl_porc_vendedor1			
	,@vl_cod_usuario_vendedor2	
	,@vl_porc_vendedor2		
	,@vl_cod_forma_pago		
	,@vl_cod_origen_venta		
	,@vl_subtotal			
	,@vl_porc_dscto_nv		
	,'P' --@ve_ingreso_usuario_dscto1 	
	,0 --@ve_monto_dscto1		
	,0 --@ve_porc_dscto2		
	,'P' --@ve_ingreso_usuario_dscto2 	
	,0 --@ve_monto_dscto2		
	,@vl_total_neto			
	,@vl_porc_iva			
	,@vl_monto_iva			 
	,@vl_total_con_iva		
	,NULL					--@ve_porc_factura_parcial
	,@vl_nom_forma_pago_otro
	,'N'
	,'GUIA_DESPACHO'
	,'N'					-- CANCELADA	
END
go