--------------------- f_op_por_asignar --------------
create FUNCTION f_op_por_asignar(@ve_cod_orden_pago numeric)
RETURNS numeric
AS
BEGIN
	declare 
		@monto_op			numeric
		,@monto_asignado	numeric

	select @monto_op = TOTAL_NETO
	from orden_pago
	where cod_orden_pago = @ve_cod_orden_pago

	select @monto_asignado = isnull(sum(pop.monto_asignado), 0)
	from participacion_orden_pago pop, participacion p
	where pop.cod_orden_pago = @ve_cod_orden_pago
	  and p.cod_participacion = pop.cod_participacion
	  and p.cod_estado_participacion in (1, 2)	-- emitida or confirmada

	return @monto_op - @monto_asignado
END