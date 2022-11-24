------------------f_get_porc_dscto_corporativo_empresa MOD MH----------------
CREATE FUNCTION [dbo].[f_get_porc_dscto_corporativo_empresa](@ve_cod_empresa numeric, @ve_fecha_inicio_vigencia datetime)
RETURNS numeric (5,2)
AS
BEGIN

declare @valor numeric(5,2)
	
	select top 1 @valor = (porc_dscto_corporativo)
	from dscto_corporativo_empresa 
	where cod_empresa = @ve_cod_empresa and
		fecha_inicio_vigencia <= @ve_fecha_inicio_vigencia
	order by fecha_inicio_vigencia	desc	
					
return isnull(@valor, 0);
end
go