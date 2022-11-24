------------------ f_fa_saldo ----------------
-- Retorna el saldo de una FA
create function [dbo].[f_fa_saldo](@ve_cod_factura numeric)
RETURNS T_PRECIO
AS
BEGIN
	declare @total_con_iva			T_PRECIO,
			@total_ingreso_pago		T_PRECIO,
			@cod_estado_factura		numeric
				
	select	@total_con_iva = total_con_iva
			,@cod_estado_factura = cod_estado_doc_sii
	from	factura
	where	cod_factura = @ve_cod_factura

	if (@cod_estado_factura = 1 or @cod_estado_factura = 4)
		return 0

	select @total_ingreso_pago = dbo.f_fa_total_ingreso_pago(cod_factura)
	from factura
	where cod_factura = @ve_cod_factura

	return @total_con_iva - @total_ingreso_pago
END
