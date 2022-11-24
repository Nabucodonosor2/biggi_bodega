--------------------  f_bodega_precio  ----------------
create FUNCTION f_bodega_precio(@ve_cod_producto varchar(20), @ve_cod_bodega numeric, @ve_fecha datetime)
RETURNS numeric
AS
BEGIN
declare
	@vl_precio		numeric

	-- Entrada Bodega
	select 	top 1 @vl_precio = i.precio
	from  	item_entrada_bodega i, entrada_bodega e
	where 	e.cod_entrada_bodega = i.cod_entrada_bodega and
      		e.cod_bodega = @ve_cod_bodega and
      		i.cod_producto = @ve_cod_producto and
      		e.fecha_entrada_bodega <= @ve_fecha
	order by e.cod_entrada_bodega desc

	return isnull(@vl_precio, 0)
END
