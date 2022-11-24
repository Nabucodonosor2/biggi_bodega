CREATE FUNCTION [dbo].[f_get_parametro_nom_cta_contable](@ve_cod_contable numeric)
RETURNS varchar(100)
AS
BEGIN

	declare @nom_contable varchar(100)

	select @nom_contable = nom_cuenta_contable
	from cuenta_contable
	where cod_cuenta_contable = dbo.f_get_parametro_cod_cta_contable(@ve_cod_contable)
			
	if @nom_contable = '' 
		set @nom_contable  = null

	return @nom_contable
END