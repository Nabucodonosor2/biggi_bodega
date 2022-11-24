CREATE PROCEDURE [dbo].[sp_part_crear_desde] (@ve_cod_usuario_emisor numeric, @ve_cod_usuario_vendedor numeric)
AS
BEGIN  
		
		declare @kl_estado_part_emitida numeric
				,@kl_estado_part_confirmada numeric
				,@vl_cod_participacion numeric
				,@vl_cod_empresa numeric
				,@vl_cod_orden_pago numeric
				,@vl_total_neto_participacion T_PRECIO
				,@vl_tipo_participacion	varchar(4)
				,@kl_cod_parametro_iva numeric
				,@kl_cod_parametro_bh numeric
				,@vl_porc_iva T_PORCENTAJE
				,@vl_porc_bh T_PORCENTAJE
				,@vl_porc_iva_bh T_PORCENTAJE
				,@vl_monto_iva T_PRECIO
				,@vl_total_con_iva T_PRECIO

		set @kl_estado_part_emitida = 1
		set @kl_estado_part_confirmada = 2
		set @kl_cod_parametro_iva = 1
		set @kl_cod_parametro_bh = 2
	
		select @vl_cod_empresa = cod_empresa from usuario where cod_usuario = @ve_cod_usuario_vendedor
 
		select @vl_tipo_participacion = tipo_participacion from empresa where cod_empresa = @vl_cod_empresa
		
		select @vl_porc_iva = valor from parametro where cod_parametro = @kl_cod_parametro_iva
		select @vl_porc_bh = valor from parametro where cod_parametro = @kl_cod_parametro_bh
		
		if (@vl_tipo_participacion = 'BH')
			set @vl_porc_iva_bh = @vl_porc_bh
		else
			set @vl_porc_iva_bh = @vl_porc_iva 
			
		execute spu_participacion 'INSERT'
								,null						--cod_participacion
								,@ve_cod_usuario_emisor		-- cod_usuario
								,@ve_cod_usuario_vendedor
								,@kl_estado_part_emitida	--cod_estado_participacion
								,@vl_tipo_participacion		--tipo_documento
								,0							--total_neto
								,0							--porc_iva
								,0							--monto_iva
								,0							--total_con_iva
								,null						--motivo_anula

		set @vl_cod_participacion = @@identity

		declare c_cursor cursor for 
		select COD_ORDEN_PAGO from ORDEN_PAGO
		where COD_ORDEN_PAGO not in (select COD_ORDEN_PAGO from PARTICIPACION_ORDEN_PAGO POP, PARTICIPACION P
									where POP.COD_PARTICIPACION = P.COD_PARTICIPACION
										AND COD_ESTADO_PARTICIPACION IN (@kl_estado_part_emitida, @kl_estado_part_confirmada))
			and COD_EMPRESA = @vl_cod_empresa

		open c_cursor 
		fetch c_cursor into @vl_cod_orden_pago
		while @@fetch_status = 0 
		begin							
			insert into PARTICIPACION_ORDEN_PAGO
				(COD_PARTICIPACION
				,COD_ORDEN_PAGO)
			values
				(@vl_cod_participacion
				,@vl_cod_orden_pago)

			fetch c_cursor into @vl_cod_orden_pago
		end
		close c_cursor
		deallocate c_cursor
		
		--calcula totales
		select @vl_total_neto_participacion = sum(total_neto)
		from orden_pago op, participacion_orden_pago pop		
		where pop.cod_participacion = @vl_cod_participacion
			and pop.cod_orden_pago = op.cod_orden_pago
		
		set @vl_monto_iva = round(@vl_total_neto_participacion * @vl_porc_iva_bh /100, 0)
		
		if (@vl_tipo_participacion = 'BH')
			set @vl_total_con_iva = @vl_total_neto_participacion - @vl_monto_iva
		else
			set @vl_total_con_iva = @vl_total_neto_participacion + @vl_monto_iva
		
		update participacion
		set TOTAL_NETO		= @vl_total_neto_participacion
			,PORC_IVA		= @vl_porc_iva_bh
			,MONTO_IVA		= @vl_monto_iva
			,TOTAL_CON_IVA	= @vl_total_con_iva
		where cod_participacion = @vl_cod_participacion

END
go