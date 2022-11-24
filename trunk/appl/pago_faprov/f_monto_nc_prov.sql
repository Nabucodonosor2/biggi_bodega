CREATE FUNCTION [dbo].[f_monto_nc_prov](@ve_cod_pago_faprov numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_monto_total	T_PRECIO
	
	SELECT @vl_monto_total = SUM(MONTO_ASIGNADO)
	FROM NCPROV_PAGO_FAPROV
	WHERE COD_PAGO_FAPROV = @ve_cod_pago_faprov
			 
	return isnull (@vl_monto_total,0);
END	
go