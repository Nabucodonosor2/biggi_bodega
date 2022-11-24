ALTER FUNCTION [dbo].[f_nv_cobrado_neto](@ve_cod_nota_venta numeric)
RETURNS T_PRECIO
AS
BEGIN
	declare
		@total_con_iva		T_PRECIO,
		@total_neto			T_PRECIO,
		@total_pago			T_PRECIO,
		@total_pago_neto	T_PRECIO

	select @total_neto = total_neto
			,@total_con_iva = total_con_iva
	from nota_venta
	where cod_nota_venta = @ve_cod_nota_venta

	select @total_pago = dbo.f_nv_total_pago(@ve_cod_nota_venta)

	if (@total_con_iva = 0)
		set @total_pago_neto = 0
	else
		set @total_pago_neto = round(@total_pago * (@total_neto / @total_con_iva), 0)

	return @total_pago_neto 
END
