alter FUNCTION f_oc_por_llegar (@ve_cod_item_orden_compra numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_cant_item_oc		T_CANTIDAD,
			@vl_cant_item_entrada	T_CANTIDAD,
			@vl_cant_por_llegar	T_CANTIDAD

	select @vl_cant_item_oc = isnull(cantidad, 0)
	from item_orden_compra
	where cod_item_orden_compra = @ve_cod_item_orden_compra
	
	set @vl_cant_item_entrada = dbo.f_oc_recibido (@ve_cod_item_orden_compra)

	set @vl_cant_por_llegar = @vl_cant_item_oc - @vl_cant_item_entrada

	return @vl_cant_por_llegar;
END
go
