alter PROCEDURE spx_abrir_nv(@ve_cod_nota_venta numeric)
AS
/*
Verificar si la NV esta cerrada si se puede abrir
para abrir debe no tener ningun documento asociado
*/
BEGIN
	declare
		@cod_orden_pago			numeric
		,@cod_participacion		numeric
		,@vl_count				numeric


	select @vl_count = count(*)
	from nota_venta
	where cod_nota_venta = @ve_cod_nota_venta
	if (@vl_count=0) begin
		select 'La NV '+convert(varchar, @ve_cod_nota_venta)+'no existe'
		return
	end 


	DECLARE C_OP CURSOR FOR  
	SELECT cod_orden_pago 
	from orden_pago
	where cod_nota_venta = @ve_cod_nota_venta
	
	OPEN C_OP
	FETCH C_OP INTO @cod_orden_pago
	WHILE @@FETCH_STATUS = 0 BEGIN	
		SELECT distinct @cod_participacion = p.cod_participacion
		from orden_pago op, participacion_orden_pago pop, participacion p
		where op.cod_orden_pago = @cod_orden_pago 
		and pop.cod_orden_pago = op.cod_orden_pago
		and p.cod_participacion = pop.cod_participacion

		if (@@ROWCOUNT > 0) begin
			select 'La NV tiene registro participacion'	MOTIVO
					,@ve_cod_nota_venta	COD_NOTA_VENTA
					,@cod_participacion COD_PARTICIPACION

			CLOSE C_OP
			DEALLOCATE C_OP
			return
		end

		FETCH C_OP INTO @cod_orden_pago
	END
	CLOSE C_OP
	DEALLOCATE C_OP

	-- se puede abrir
	delete orden_pago
	where cod_nota_venta = @ve_cod_nota_venta

	update nota_venta
	set cod_estado_nota_venta = 4 -- confirmada
	where cod_nota_venta = @ve_cod_nota_venta

	-- cambia el estado de todas las OC relacionasa de estado CERRADA(3) a EMITIDA(1)
	update orden_compra
	set cod_estado_orden_compra = 1	--emitida
	where cod_nota_venta = @ve_cod_nota_venta
	  and cod_estado_orden_compra = 3	-- cerrada

	select 'La NV se dejo en estado confirmada'
END
