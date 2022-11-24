ALTER PROCEDURE [dbo].[spu_participacion] (@ve_operacion					varchar(20)
									,@ve_cod_participacion			numeric
									,@ve_cod_usuario				numeric = NULL
									,@ve_cod_usuario_vendedor		numeric = NULL
									,@ve_cod_estado_participacion	numeric = NULL
									,@ve_tipo_documento				varchar(10) = NULL
									,@ve_total_neto					T_PRECIO = NULL
									,@ve_porc_iva					T_PORCENTAJE = NULL
									,@ve_monto_iva					T_PRECIO = NULL
									,@ve_total_con_iva				T_PRECIO = NULL
									,@ve_motivo_anula				varchar(100) = NULL
									,@ve_referencia					varchar(100) = NULL)


AS
BEGIN  
		declare @kl_estado_part_emitida numeric
				,@kl_estado_part_confirmada numeric
				,@kl_estado_part_anulada numeric
				,@vl_cod_estado_participacion numeric
				,@vl_cod_usuario_dir numeric
				,@vl_cod_empresa numeric
				,@vl_fecha_participacion varchar(10)
				,@vl_cod_faprov numeric
				,@vl_cod_participacion numeric

		set @kl_estado_part_emitida = 1
		set @kl_estado_part_confirmada = 2
		set @kl_estado_part_anulada = 3

		if (@ve_operacion='INSERT')
			insert into PARTICIPACION
			   (FECHA_PARTICIPACION
			   ,COD_USUARIO
			   ,COD_USUARIO_VENDEDOR
			   ,COD_ESTADO_PARTICIPACION
			   ,TIPO_DOCUMENTO
			   ,TOTAL_NETO
			   ,PORC_IVA
			   ,MONTO_IVA
			   ,TOTAL_CON_IVA
			   ,MOTIVO_ANULA
			   ,REFERENCIA)
			values
			   (getdate()
			   ,@ve_cod_usuario
			   ,@ve_cod_usuario_vendedor
			   ,@ve_cod_estado_participacion
			   ,@ve_tipo_documento
			   ,@ve_total_neto
			   ,@ve_porc_iva
			   ,@ve_monto_iva
			   ,@ve_total_con_iva
			   ,null	--MOTIVO_ANULA
			   ,@ve_referencia)
			
		else if (@ve_operacion='UPDATE')
		begin
			select @vl_cod_estado_participacion = cod_estado_participacion
			from participacion
			where COD_PARTICIPACION = @ve_cod_participacion

			update PARTICIPACION
			set COD_ESTADO_PARTICIPACION = @ve_cod_estado_participacion
				,TIPO_DOCUMENTO			= @ve_tipo_documento
				,TOTAL_NETO				= @ve_total_neto
				,PORC_IVA				= @ve_porc_iva
				,MONTO_IVA				= @ve_monto_iva
				,TOTAL_CON_IVA			= @ve_total_con_iva
				,MOTIVO_ANULA			= @ve_motivo_anula
				,REFERENCIA				= @ve_referencia
			where COD_PARTICIPACION = @ve_cod_participacion

			if (@vl_cod_estado_participacion = @kl_estado_part_confirmada) and (@ve_cod_estado_participacion = @kl_estado_part_anulada)
			begin
				select @vl_cod_usuario_dir = valor from parametro where cod_parametro = 31 				
				if(@ve_cod_usuario_vendedor = @vl_cod_usuario_dir)--debe anular la recepción de FA ficticia
					update faprov
					set cod_estado_faprov = 5 --anulada
						,cod_usuario_anula = 1
						,motivo_anula = 'ANULACIÓN DE PARTICIPACIÓN DIRECTORIO COD: '+@ve_cod_participacion
					where origen_faprov = 'PARTICIPACION'
						and nro_faprov = @ve_cod_participacion
			end

		end
		else if (@ve_operacion='CONFIRMA')
		begin
			select @vl_cod_usuario_dir = valor from parametro where cod_parametro = 31 				
			if(@ve_cod_usuario_vendedor = @vl_cod_usuario_dir)--debe crear la recepción de FA ficticia
			begin
				select @vl_cod_empresa = cod_empresa from usuario where cod_usuario = @ve_cod_usuario_vendedor
				select @vl_fecha_participacion = convert(varchar(10), fecha_participacion , 103) from participacion where COD_PARTICIPACION = @ve_cod_participacion

				exec spu_faprov 'INSERT'
							,null					--@ve_cod_faprov
							,@ve_cod_usuario
							,@vl_cod_empresa
							,2						--@ve_cod_tipo_faprov = exenta
							,2						--@ve_cod_estado_faprov = aprobada
							,@ve_cod_participacion	--@ve_nro_faprov
							,@vl_fecha_participacion --@ve_fecha_faprov = fecha participacion
							,@ve_total_neto
							,0						--@ve_monto_iva
							,@ve_total_neto			--@ve_total_con_iva
							,null					--@ve_cod_usuario_anula
							,null					--@ve_motivo_anula
							,'PARTICIPACION'		--@ve_origen_faprov
							,null					--@ve_cod_cuenta_compra
					
					set @vl_cod_faprov = @@identity

					exec spu_item_faprov 'INSERT'
								,null					--@ve_cod_item_faprov
								,@vl_cod_faprov
								,@ve_cod_participacion	--@ve_cod_doc
								,@ve_total_neto			--@ve_monto_asignado
			end
		end
		 						
END
go