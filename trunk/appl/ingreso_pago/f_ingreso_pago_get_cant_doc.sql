----------------------- f_ingreso_pago_get_cant_doc -----------------------
create FUNCTION [dbo].[f_ingreso_pago_get_cant_doc](@ve_cod_ingreso_pago numeric)
RETURNS numeric
AS 
BEGIN
DECLARE @vl_cantidad	numeric

	select @vl_cantidad = count(cod_doc_ingreso_pago)
	from doc_ingreso_pago
	where cod_ingreso_pago = @ve_cod_ingreso_pago
	
	RETURN isnull(@vl_cantidad,0);
END
go




