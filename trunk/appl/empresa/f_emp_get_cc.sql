-----------------------------  f_emp_get_cc ------------------
create FUNCTION f_emp_get_cc(@ve_cod_empresa numeric)
RETURNS numeric
AS
-- Obtiene el centro de costo asociado a la empresa, si no tiene CC asume el 001 = COMERCIAL
BEGIN
	declare @cod_centro_costo		varchar(30)
	
	select TOP 1 @cod_centro_costo = COD_CENTRO_COSTO
	from 	centro_costo_empresa
	where 	COD_EMPRESA = @ve_cod_empresa

	if @@ROWCOUNT = 0
		select @cod_centro_costo = '001'	-- COMERCIAL

	return @cod_centro_costo
END	

