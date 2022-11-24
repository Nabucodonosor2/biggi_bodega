--------------------f_nv_get_ct_con_orden ---------------------
--	Dado un item de nota de venta trae la cantidad que se registra en orden compra
CREATE FUNCTION [dbo].[f_nv_get_ct_con_orden](@ve_cod_item_nota_venta numeric)
RETURNS T_CANTIDAD
AS
BEGIN
DECLARE @cantidad	numeric
	select @cantidad = isnull(SUM(CANTIDAD), 0)
	from ITEM_ORDEN_COMPRA
	where COD_ITEM_NOTA_VENTA = @ve_cod_item_nota_venta

	RETURN @cantidad
END
go