-------------------- spu_solucion_sw ---------------------------------
alter PROCEDURE spu_solucion_sw(@ve_operacion				varchar(20)
									,@ve_cod_solucion_sw		numeric
									,@ve_cod_mantencion_sw		numeric = null
									,@ve_cod_desarrollador_sw	numeric = null
									,@ve_cod_estado_solucion_sw	numeric = null
									,@ve_nro_iteracion			numeric = null
									,@ve_solucion_cliente		text = null
									,@ve_solucion_interna		text = null
									,@ve_minutos				numeric = null
									,@ve_respuesta_cliente		text = null)
AS
BEGIN
	declare
		@cod_solucion_sw		numeric

	if (@ve_operacion='INSERT') begin
		insert into solucion_sw 
			(cod_mantencion_sw
			,cod_desarrollador_sw
			,cod_estado_solucion_sw
			,nro_iteracion
			,fecha_inicio
			,fecha_termino
			,solucion_cliente
			,solucion_interna
			,minutos
			,respuesta_cliente)
		values 
			(@ve_cod_mantencion_sw
			,@ve_cod_desarrollador_sw
			,@ve_cod_estado_solucion_sw
			,@ve_nro_iteracion
			,null
			,null
			,@ve_solucion_cliente
			,@ve_solucion_interna
			,@ve_minutos
			,@ve_respuesta_cliente)
	end
	else if (@ve_operacion='UPDATE') begin
		update solucion_sw
		set cod_mantencion_sw = @ve_cod_mantencion_sw
			,cod_desarrollador_sw = @ve_cod_desarrollador_sw
			,cod_estado_solucion_sw = @ve_cod_estado_solucion_sw
			,nro_iteracion = @ve_nro_iteracion
			,solucion_cliente = @ve_solucion_cliente
			,solucion_interna = @ve_solucion_interna
			,minutos = @ve_minutos
			,respuesta_cliente = @ve_respuesta_cliente
		where cod_solucion_sw = @ve_cod_solucion_sw

		-- actualiza fecha_inicio y fecha_termino si corresponde
		declare 
			@K_ANALISTA_ASIGNADO	numeric
			,@K_TERMINADO			numeric

			,@fecha_inicio		datetime
			,@fecha_termino		datetime

		set @K_ANALISTA_ASIGNADO = 20
		set @K_TERMINADO = 80

		select @fecha_inicio = fecha_inicio
				,@fecha_termino = fecha_termino
		from solucion_sw
		where cod_solucion_sw = @ve_cod_solucion_sw

		if (@fecha_inicio is null and @ve_cod_estado_solucion_sw = @K_ANALISTA_ASIGNADO)
			update solucion_sw
			set fecha_inicio = getdate()
			where cod_solucion_sw = @ve_cod_solucion_sw
	
		if (@fecha_termino is null and @ve_cod_estado_solucion_sw = @K_TERMINADO)
			update solucion_sw
			set fecha_termino = getdate()
			where cod_solucion_sw = @ve_cod_solucion_sw
	end 
	else if (@ve_operacion='DELETE')
		delete solucion_sw  
		where cod_solucion_sw = @ve_cod_solucion_sw
END
go
