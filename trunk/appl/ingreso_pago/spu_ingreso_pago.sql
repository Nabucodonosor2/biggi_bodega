-------------------- spu_ingreso_pago ---------------------------------
ALTER PROCEDURE [dbo].[spu_ingreso_pago]
			(@ve_operacion					varchar(20)
			,@ve_cod_ingreso_pago			numeric
			,@ve_cod_usuario				numeric		 = NULL
			,@ve_cod_empresa				numeric		 = NULL
			,@ve_otro_ingreso				numeric		 = NULL
			,@ve_otro_gasto					numeric		 = NULL
			,@ve_cod_estado_ingreso_pago	numeric		 = NULL
			,@ve_cod_usuario_anula			numeric		 = NULL
			,@ve_motivo_anula				varchar(100) = NULL
			,@ve_cod_usuario_confirma		numeric		 = NULL
			,@ve_otro_anticipo				numeric		 = NULL
			,@ve_cod_proyecto_ingreso		numeric		 = NULL)

AS
BEGIN
	declare		@kl_cod_estado_ingreso_pago_anula		numeric,
				@kl_cod_estado_ingreso_pago_confirma	numeric,
				@vl_cod_usuario_anula					numeric,
				@vl_cod_ingreso_pago_factura			numeric,
				@vl_cod_doc_ingreso_pago				numeric,
				@vl_monto								numeric,
				@vl_monto_doc_total						numeric


	set @kl_cod_estado_ingreso_pago_anula	 = 3  --- estado_ingreso_pago = anulada
	set @kl_cod_estado_ingreso_pago_confirma = 2  --- estado_ingreso_pago = confirma

		if (@ve_operacion='UPDATE') 
			begin
				UPDATE ingreso_pago		
				SET		
							cod_empresa					=	@ve_cod_empresa	
							,otro_ingreso				=	@ve_otro_ingreso
							,otro_gasto					=	@ve_otro_gasto
							,cod_estado_ingreso_pago	=	@ve_cod_estado_ingreso_pago
							,otro_anticipo				=	@ve_otro_anticipo
							,cod_proyecto_ingreso		=	@ve_cod_proyecto_ingreso
				
				WHERE cod_ingreso_pago = @ve_cod_ingreso_pago
				if (@ve_cod_estado_ingreso_pago = @kl_cod_estado_ingreso_pago_anula) and (@vl_cod_usuario_anula is NULL) -- estado del ingreso_pago = anulada 
					update ingreso_pago
					set fecha_anula			= getdate ()
						,motivo_anula		= @ve_motivo_anula			
						,cod_usuario_anula	= @ve_cod_usuario_anula				
					where cod_ingreso_pago  = @ve_cod_ingreso_pago
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into ingreso_pago
					(fecha_ingreso_pago
					,cod_usuario
					,cod_empresa
					,otro_ingreso
					,otro_gasto
					,cod_estado_ingreso_pago
					,cod_usuario_confirma
					,otro_anticipo
					,cod_proyecto_ingreso)
				values 
					(getdate()
					,@ve_cod_usuario	
					,@ve_cod_empresa	
					,@ve_otro_ingreso
					,@ve_otro_gasto
					,@ve_cod_estado_ingreso_pago
					,@ve_cod_usuario_confirma		
					,@ve_otro_anticipo
					,@ve_cod_proyecto_ingreso)
			end 
		else if(@ve_operacion='CONFIRMA')
			begin
				if (@ve_cod_estado_ingreso_pago = @kl_cod_estado_ingreso_pago_confirma) -- estado del ingreso_pago = confirmada
					update ingreso_pago
						set fecha_confirma			= getdate ()
							,cod_usuario_confirma	= @ve_cod_usuario_confirma				
						where cod_ingreso_pago		= @ve_cod_ingreso_pago					

					DECLARE C_DOC_INGRESO_PAGO CURSOR FOR 
					select cod_doc_ingreso_pago, monto_doc
					from doc_ingreso_pago
					where cod_ingreso_pago = @ve_cod_ingreso_pago

					declare
						@cod_doc_ingreso_pago		numeric,
						@monto_doc					T_PRECIO,
						@cod_ingreso_pago_factura	numeric,
						@saldo_por_relacionar		T_PRECIO,
						@monto_doc_asignado			T_PRECIO
					OPEN C_DOC_INGRESO_PAGO
					FETCH C_DOC_INGRESO_PAGO INTO @cod_doc_ingreso_pago, @monto_doc
					WHILE @@FETCH_STATUS = 0 BEGIN
						DECLARE C_INGRESO_PAGO_FA CURSOR FOR 
						select cod_ingreso_pago_factura
								,dbo.f_ingreso_pago_saldo_por_relacionar(cod_ingreso_pago_factura) saldo_por_relacionar
						from ingreso_pago_factura
						where cod_ingreso_pago = @ve_cod_ingreso_pago
						OPEN C_INGRESO_PAGO_FA
						FETCH C_INGRESO_PAGO_FA INTO @cod_ingreso_pago_factura, @saldo_por_relacionar
						WHILE @@FETCH_STATUS = 0 BEGIN
							if (@saldo_por_relacionar > @monto_doc)
								set @monto_doc_asignado = @monto_doc
							else
								set @monto_doc_asignado = @saldo_por_relacionar
							set @monto_doc = @monto_doc - @monto_doc_asignado

							insert into monto_doc_asignado
								(cod_doc_ingreso_pago
								,cod_ingreso_pago_factura
								,monto_doc_asignado)
							values
								(@cod_doc_ingreso_pago
								,@cod_ingreso_pago_factura
								,@monto_doc_asignado)
	
							if (@monto_doc = 0)
								BREAK 
							FETCH C_INGRESO_PAGO_FA INTO @cod_ingreso_pago_factura, @saldo_por_relacionar
						END
						CLOSE C_INGRESO_PAGO_FA
						DEALLOCATE C_INGRESO_PAGO_FA
	
						FETCH C_DOC_INGRESO_PAGO INTO @cod_doc_ingreso_pago, @monto_doc
					END
					CLOSE C_DOC_INGRESO_PAGO
					DEALLOCATE C_DOC_INGRESO_PAGO

					---------------------------------
					-- Busca FA asociadas a la NV que tengan saldo > 0, para ver si se deben reasginar pagos desde NV a FA
					declare C_FA cursor for
						select f.cod_factura
						from ingreso_pago_factura ipf, factura f
						where ipf.cod_ingreso_pago = @ve_cod_ingreso_pago
						  and ipf.tipo_doc = 'NOTA_VENTA'
						  and f.cod_doc = ipf.cod_doc
						  and f.cod_tipo_factura = 1	-- venta
						  and f.cod_estado_doc_sii in (2, 3)	-- confirmada
						  and dbo.f_factura_get_saldo(f.cod_factura) > 0

					declare 
						@cod_factura			numeric

					OPEN C_FA
					FETCH C_FA INTO @cod_factura
					WHILE @@FETCH_STATUS = 0 BEGIN
						exec spu_factura 'REASIGNA_PAGO', @cod_factura
						FETCH C_FA INTO @cod_factura
					END
					CLOSE C_FA
					DEALLOCATE C_FA
					----------------------------------	


			end
		else if (@ve_operacion='DELETE_ALL') 
				begin
					delete ingreso_pago_factura
    				where cod_ingreso_pago = @ve_cod_ingreso_pago 
					
					delete ingreso_pago
					where cod_ingreso_pago = @ve_cod_ingreso_pago
				end 

END

