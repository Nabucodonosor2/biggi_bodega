CREATE FUNCTION [dbo].[f_nv_oc_por_item](@ve_cod_item_nota_venta numeric)
RETURNS varchar(100)
AS
BEGIN
	declare @cod_orden_compra numeric,
			@cadena varchar(100)

	
	DECLARE C_TEMPO CURSOR FOR  
	SELECT IOC.COD_ORDEN_COMPRA 
	FROM ITEM_NOTA_VENTA INV, ITEM_ORDEN_COMPRA IOC
    WHERE INV.COD_ITEM_NOTA_VENTA = @ve_cod_item_nota_venta AND
		  INV.COD_ITEM_NOTA_VENTA = IOC.COD_ITEM_NOTA_VENTA
	
	set @cadena = ''
	
	OPEN C_TEMPO
	FETCH C_TEMPO INTO @cod_orden_compra

	WHILE @@FETCH_STATUS = 0
		BEGIN
			SET @cadena = @cadena + CAST(@cod_orden_compra AS varchar(12))+' - '
			FETCH C_TEMPO INTO @cod_orden_compra
		END
	CLOSE C_TEMPO
	DEALLOCATE C_TEMPO
	
	if (@cadena <> '')
		set @cadena = substring(@cadena, 1, len(@cadena) - 1)

		
	return @cadena

END
go