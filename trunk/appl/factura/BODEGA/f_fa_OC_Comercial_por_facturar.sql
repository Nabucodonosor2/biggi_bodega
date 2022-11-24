------------------------- f_fa_OC_Comercial_por_facturar -----------------------
alter  FUNCTION f_fa_OC_Comercial_por_facturar(@ve_cod_item_orden_compra numeric)
RETURNS T_CANTIDAD
AS
BEGIN
	declare
		@vl_cantidad_oc		T_CANTIDAD
		,@vl_cantidad_fa	T_CANTIDAD
		,@vl_cod_orden_compra	numeric

	set @vl_cantidad_oc = 0
	set @vl_cantidad_fa = 0

	select @vl_cantidad_oc = cantidad
			,@vl_cod_orden_compra = cod_orden_compra
	from BIGGI.dbo.ITEM_ORDEN_COMPRA I
	where I.COD_ITEM_ORDEN_COMPRA = @ve_cod_item_orden_compra

	select @vl_cantidad_fa = isnull(sum(i.cantidad), 0)
	from factura f, item_factura i
	where f.cod_tipo_factura = 3	-- OC COmercial
	  and f.cod_estado_doc_sii in (2,3)
	  and f.nro_orden_compra = convert(varchar, @vl_cod_orden_compra)
	  and i.cod_factura = f.cod_factura
	  and i.cod_item_doc = @ve_cod_item_orden_compra
	
	return @vl_cantidad_oc - @vl_cantidad_fa
END
