--------------------  f_bodega_stock_cero  ----------------
alter FUNCTION f_bodega_stock_cero(@ve_cod_producto varchar(20), @ve_cod_bodega numeric, @ve_fecha datetime)
RETURNS numeric(10,2)
AS
-- Es igual a f_bodega_stock, pero si da stock negativo retorna CERO
BEGIN
declare
	@stock_total		T_CANTIDAD

	set @stock_total = dbo.f_bodega_stock(@ve_cod_producto, @ve_cod_bodega, @ve_fecha)
	if (@stock_total < 0)
		set @stock_total = 0

	return @stock_total
END