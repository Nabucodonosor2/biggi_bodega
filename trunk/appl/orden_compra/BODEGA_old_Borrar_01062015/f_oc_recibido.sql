-------------------------- f_oc_recibido --------------------
alter FUNCTION f_oc_recibido (@ve_cod_item_orden_compra numeric)
RETURNS numeric
AS
BEGIN
	declare 
			@vl_cant_item_entrada	T_CANTIDAD

	select @vl_cant_item_entrada = isnull(sum(i.cantidad), 0)
	from item_entrada_bodega i, entrada_bodega e
	where i.cod_item_doc = @ve_cod_item_orden_compra
	  and e.cod_entrada_bodega = i.cod_entrada_bodega
	  and e.tipo_doc = 'ORDEN_COMPRA'

	return @vl_cant_item_entrada
END
