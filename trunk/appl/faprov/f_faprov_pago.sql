--------------------- f_faprov_pago --------------
CREATE FUNCTION f_faprov_pago(@ve_cod_faprov numeric)
RETURNS numeric
AS
BEGIN
	declare @monto	numeric

	set @monto = 0
	select @monto = monto_asignado
	from pago_faprov_faprov	pff, pago_faprov pf
	where pff.cod_faprov = @ve_cod_faprov
	  and pf.cod_pago_faprov = pff.cod_pago_faprov
	  and pf.cod_estado_pago_faprov = 2 -- confirmada

	return @monto
END