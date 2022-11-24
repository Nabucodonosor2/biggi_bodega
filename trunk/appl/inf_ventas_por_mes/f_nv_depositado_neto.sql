-- Retorna el monto depositado NETO, esta funcion es usada en los informes
CREATE FUNCTION f_nv_depositado_neto(@ve_cod_item_nota_venta numeric)
RETURNS T_PRECIO
AS
BEGIN
	return 0	   
END
go
