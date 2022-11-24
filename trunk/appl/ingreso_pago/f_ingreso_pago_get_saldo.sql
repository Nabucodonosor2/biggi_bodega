----------------------- f_ingreso_pago_get_saldo ---------------------
CREATE FUNCTION [dbo].[f_ingreso_pago_get_saldo](@ve_cod_ingreso_pago numeric, @ve_cod_doc_ingreso_pago numeric)
RETURNS numeric
AS 
BEGIN
DECLARE @vl_monto_doc	numeric

	select @vl_monto_doc = sum(monto_doc)
	from doc_ingreso_pago 
	where cod_ingreso_pago = @ve_cod_ingreso_pago and
			cod_doc_ingreso_pago < @ve_cod_doc_ingreso_pago
	
	RETURN isnull(@vl_monto_doc,0);
END
GO