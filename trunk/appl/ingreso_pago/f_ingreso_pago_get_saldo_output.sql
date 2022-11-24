CREATE FUNCTION [dbo].[f_ingreso_pago_get_saldo_output](@ve_cod_ingreso_pago numeric)
RETURNS numeric
AS 
BEGIN
DECLARE @vl_monto_output	numeric

	select top 1 @vl_monto_output = dbo.f_ingreso_pago_get_saldo(cod_ingreso_pago , cod_doc_ingreso_pago+1)
	from doc_ingreso_pago
	where cod_ingreso_pago = @ve_cod_ingreso_pago
	order by cod_doc_ingreso_pago desc

	RETURN isnull(@vl_monto_output,0);
END
go