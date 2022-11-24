-----------------------------  f_nc_get_cc ------------------
create FUNCTION f_nc_get_cc(@ve_cod_nota_credito numeric)
RETURNS numeric
AS
-- Obtiene el centro de costo asociado a la NC si es null busca el asociados a empresa, si no tiene CC asume el 001 = COMERCIAL
BEGIN
	declare 
		@cod_centro_costo		varchar(30)
		,@cod_empresa			numeric

	select @cod_centro_costo = cod_centro_costo
			,@cod_empresa = cod_empresa
	from nota_credito
	where cod_nota_credito = @ve_cod_nota_credito

	if (@cod_centro_costo is null)
		set @cod_centro_costo = dbo.f_emp_get_cc(@cod_empresa)

	return @cod_centro_costo
END	

