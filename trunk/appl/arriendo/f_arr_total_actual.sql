--------------------  f_arr_total_actual  ----------------
alter FUNCTION f_arr_total_actual(@ve_cod_arriendo numeric)
RETURNS numeric
AS
BEGIN
declare
	@total		numeric

	select @total = sum(round(dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) * I.PRECIO, 0))
	from ARRIENDO A, ITEM_ARRIENDO I
	where A.COD_ARRIENDO = @ve_cod_arriendo
  	  and I.COD_ARRIENDO = A.COD_ARRIENDO
  	  and dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) > 0

	RETURN @total
END