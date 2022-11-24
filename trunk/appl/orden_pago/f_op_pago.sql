--------------------- f_op_pago --------------
alter FUNCTION f_op_pago(@ve_cod_orden_pago numeric)
RETURNS numeric
AS
BEGIN
	declare 
		@cod_participacion	numeric
		,@pago_participacion	numeric
		,@cod_orden_pago		numeric
		,@monto_op	numeric
		,@monto_pago		numeric
		
	select @cod_participacion = p.cod_participacion
	from participacion_orden_pago pop, participacion p
	where pop.cod_orden_pago = @ve_cod_orden_pago
	  and p.cod_participacion = pop.cod_participacion
	  and p.cod_estado_participacion = 2 -- confirmada
	if (@@rowcount = 0)
		return 0


	set @pago_participacion = dbo.f_part_pago(@cod_participacion) 

	-- los pagos se consignan por rebalse
	DECLARE C_OP CURSOR FOR  
	select pop.cod_orden_pago
			,op.total_neto
	from participacion_orden_pago pop, orden_pago op
	where pop.cod_participacion = @cod_participacion
	  and op.cod_orden_pago = pop.cod_orden_pago
	order by cod_orden_pago_participacion

	set @monto_pago = 0
	OPEN C_OP
	FETCH C_OP INTO @cod_orden_pago, @monto_op
	WHILE @@FETCH_STATUS = 0 BEGIN	
		if (@pago_participacion < @monto_op)
			set @monto_op = @pago_participacion

		set @pago_participacion = @pago_participacion  - @monto_op

		if (@cod_orden_pago = @ve_cod_orden_pago)
			set @monto_pago = @monto_op
			

		FETCH C_OP INTO  @cod_orden_pago, @monto_op
	END
	CLOSE C_OP
	DEALLOCATE C_OP

	return @monto_pago
END