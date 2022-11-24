CREATE FUNCTION [dbo].[f_faprov_get_oc](@ve_cod_faprov numeric)
RETURNS varchar(30)
AS
BEGIN
	declare @nro_doc	varchar(30),
			@cant_doc	numeric

	SELECT top 1 @nro_doc =  convert(varchar(10), COD_DOC)
	FROM ITEM_FAPROV
	WHERE COD_FAPROV = @ve_cod_faprov
	order by COD_DOC DESC

	SELECT	@cant_doc = count(*)
	FROM ITEM_FAPROV
	WHERE COD_FAPROV = @ve_cod_faprov
	
	if(@cant_doc > 1)
		set @nro_doc = @nro_doc + '+'

	return @nro_doc;
END