--------------------- f_part_pago --------------
CREATE FUNCTION f_part_pago(@ve_cod_participacion numeric)
RETURNS numeric
AS
BEGIN
	declare 
		@monto_pago		numeric
		
	select @monto_pago = isnull(sum(dbo.f_iffaprov_pago(itfa.cod_item_faprov)), 0)
	from item_faprov itfa, faprov fa
	where itfa.cod_doc = @ve_cod_participacion
	  and fa.cod_faprov = itfa.cod_faprov
	  and fa.origen_faprov = 'PARTICIPACION'
	  and fa.cod_estado_faprov = 2 -- confirmada

	return @monto_pago
END