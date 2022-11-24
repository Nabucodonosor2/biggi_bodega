------------------ f_fa_total_ingreso_pago ----------------
CREATE function [dbo].[f_fa_total_ingreso_pago](@ve_cod_factura numeric)
RETURNS T_PRECIO
AS
BEGIN
	declare @total_ingreso_pago		T_PRECIO,
			@K_ESTADO_CONFIRMADA numeric

	set @K_ESTADO_CONFIRMADA = 2

	select	@total_ingreso_pago = isnull(sum(monto_asignado),0)
	from	ingreso_pago_factura ipf, ingreso_pago ip
	where	cod_doc = @ve_cod_factura and
			tipo_doc = 'FACTURA'
	and		ip.cod_ingreso_pago = ipf.cod_ingreso_pago
	and		cod_estado_ingreso_pago = @K_ESTADO_CONFIRMADA

	return @total_ingreso_pago
END
go