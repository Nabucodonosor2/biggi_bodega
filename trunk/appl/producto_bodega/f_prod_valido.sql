ALTER FUNCTION [dbo].[f_prod_valido] (@ve_cod_producto varchar(30))
RETURNS varchar(1)
AS
BEGIN
	declare @kl_param_sistema numeric,
			@vl_sistema varchar(100),
			@vl_sistema_valido varchar(10)

	set @kl_param_sistema = 3
	set @vl_sistema = dbo.f_get_parametro(@kl_param_sistema)
	
	select @vl_sistema_valido = sistema_valido
	from producto
	where cod_producto = @ve_cod_producto
 
	if (@vl_sistema = 'COMERCIAL')
		return substring(@vl_sistema_valido, 1, 1)
	else if (@vl_sistema = 'BODEGA')
		return substring(@vl_sistema_valido, 2, 1)
	else if (@vl_sistema = 'CATERING')
		return substring(@vl_sistema_valido, 3, 1)
	else if (@vl_sistema = 'TODOINOX')
		return substring(@vl_sistema_valido, 4, 1)

	return 'N';
END