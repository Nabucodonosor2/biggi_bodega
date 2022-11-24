ALTER FUNCTION f_sol_recibido(@ve_cod_solicitud_compra numeric)
RETURNS numeric
AS
BEGIN
	declare 
		@vl_cant_item_entrada	T_CANTIDAD

	select @vl_cant_item_entrada = isnull(sum(i.cantidad), 0)
	from solicitud_compra s, orden_compra o, entrada_bodega e, item_entrada_bodega i
	where s.cod_solicitud_compra = @ve_cod_solicitud_compra
	  and o.TIPO_ORDEN_COMPRA = 'SOLICITUD_COMPRA'
	  and o.cod_doc = s.cod_solicitud_compra
	  and e.tipo_doc = 'ORDEN_COMPRA'
	  and e.cod_doc = o.cod_orden_compra
	  and i.cod_entrada_bodega = e.cod_entrada_bodega
	  and i.cod_producto = s.cod_producto

	return @vl_cant_item_entrada
END

