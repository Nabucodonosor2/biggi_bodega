-- Obtiene la cuenta corriente de un negocio asociado al cliente que entra como parametro
CREATE FUNCTION f_emp_get_cta_cte(@ve_cod_empresa numeric)
RETURNS numeric
AS
BEGIN
	declare @cod_cuenta_corriente  			numeric,
			@K_PARAM_CTA_CTE_COMERCIAL		numeric
	
	SET @K_PARAM_CTA_CTE_COMERCIAL = 33

	select TOP 1 @cod_cuenta_corriente = COD_CUENTA_CORRIENTE 
	from 	EMPRESA_CUENTA_CORRIENTE
	where 	COD_EMPRESA = @ve_cod_empresa

	if @@ROWCOUNT = 0
		select @cod_cuenta_corriente = dbo.f_get_parametro(@K_PARAM_CTA_CTE_COMERCIAL)

	return @cod_cuenta_corriente;
END	
go