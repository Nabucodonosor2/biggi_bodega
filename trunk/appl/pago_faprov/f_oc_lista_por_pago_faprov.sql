CREATE FUNCTION [dbo].[f_oc_lista_por_pago_faprov](@ve_cod_faprov numeric)
RETURNS varchar(100)
AS
BEGIN
	declare @cod_orden_compra numeric,
			@cadena varchar(100)

	
	DECLARE C_TEMPO CURSOR FOR  
	SELECT OC.COD_ORDEN_COMPRA 
	FROM ITEM_FAPROV IT, ORDEN_COMPRA OC
    WHERE IT.COD_FAPROV = @ve_cod_faprov AND
		  IT.COD_DOC = OC.COD_ORDEN_COMPRA
	
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