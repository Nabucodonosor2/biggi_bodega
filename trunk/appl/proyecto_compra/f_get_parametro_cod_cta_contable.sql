CREATE FUNCTION [dbo].[f_get_parametro_cod_cta_contable](@ve_cod_contable numeric)
RETURNS numeric
AS
BEGIN
	
	declare @cod_cta_contable    numeric

    select @cod_cta_contable = cod_cuenta_contable
    from cuenta_contable
    where cod_cuenta_contable = @ve_cod_contable

    return @cod_cta_contable
END