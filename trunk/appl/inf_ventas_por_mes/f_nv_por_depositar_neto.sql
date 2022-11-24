-- Retorna el monto por depositar neto, esta funcion es usada en los informes
CREATE FUNCTION f_nv_por_depositar_neto(@ve_cod_item_nota_venta numeric)
RETURNS T_PRECIO
AS
BEGIN
	return 0	   
END
go
