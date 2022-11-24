--------------------f_nv_get_ct_con_preorden ---------------------
-- Dado un item de nota de venta trae la cantidad que se registra en preorden
CREATE FUNCTION [dbo].[f_nv_get_ct_con_preorden](@ve_cod_item_nota_venta numeric)
RETURNS T_CANTIDAD
AS
BEGIN
DECLARE @cantidad	numeric
	select @cantidad = isnull(SUM(CANTIDAD), 0)
	from PRE_ORDEN_COMPRA
	where COD_ITEM_NOTA_VENTA = @ve_cod_item_nota_venta

	RETURN @cantidad
END
go