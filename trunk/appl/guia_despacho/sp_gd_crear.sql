CREATE PROCEDURE [dbo].[sp_gd_crear] (@ve_cod_nota_venta numeric, @ve_cod_usuario numeric)
AS
BEGIN  
	
	declare @vl_cod_empresa numeric,
			@vl_cod_sucursal_despacho numeric,
			@vl_cod_persona numeric,
			@vl_referencia varchar(100), 
			@vl_nro_orden_compra varchar(40)
			
	select @vl_cod_empresa = cod_empresa
	      ,@vl_cod_sucursal_despacho = cod_sucursal_despacho
	      ,@vl_cod_persona = cod_persona
	      ,@vl_referencia = referencia
	      ,@vl_nro_orden_compra = nro_orden_compra
	from nota_venta
	where cod_nota_venta = @ve_cod_nota_venta
		
		
	execute spu_guia_despacho 
	'INSERT' 
	,NULL -- cod_guia_despacho = identity
	,NULL -- cod_usuario_impresion
	,@ve_cod_usuario 
	,NULL -- nro_guia_despacho		
	,1 -- cod_estado_doc_sii = emitida
	,@vl_cod_empresa 
	,@vl_cod_sucursal_despacho
	,@vl_cod_persona
	,@vl_referencia 
	,@vl_nro_orden_compra
	,NULL -- obs
	,NULL -- retirado_por
	,NULL -- rut_retirado_por
	,NULL -- dig_verif_retirado_por
	,NULL -- guia_transporte
	,NULL -- patente
	,NULL -- cod_factura
	,NULL -- cod_bodega
	,1 -- cod_tipo_guia_despacho = venta
	,@ve_cod_nota_venta 
	,NULL -- motivo_anula
	,NULL -- cod_usuario_anula 		 

END
go
