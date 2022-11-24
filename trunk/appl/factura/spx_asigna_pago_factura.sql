ALTER PROCEDURE [dbo].[spx_asigna_pago_factura](@ve_cod_factura numeric)
AS
BEGIN  
		declare @vl_nro_factura numeric,
				@vl_cod_doc		numeric,
				@vl_total_fa	T_PRECIO,
				@vl_cod_ingreso_pago_factura numeric,
				@vl_cod_ingreso_pago numeric,
				@vl_monto_asignado T_PRECIO,
				@vl_new_ingreso_pago_factura numeric,
				@vl_cod_monto_doc_asignado numeric, 
				@vl_monto_doc_asignado T_PRECIO,
				@vl_cod_doc_ingreso_pago numeric,
				@vl_por_asignar_fa T_PRECIO

		select @vl_nro_factura = nro_factura
			,@vl_cod_doc = cod_doc
			,@vl_total_fa = total_con_iva
		from factura
		where  cod_factura = @ve_cod_factura

		if (@vl_cod_doc is not null)--cod de NV
		begin
			declare c_ingreso_pago_factura cursor for 
			select cod_ingreso_pago_factura
				,cod_ingreso_pago
				,monto_asignado
			from ingreso_pago_factura
			where tipo_doc = 'NOTA_VENTA'
				and cod_doc = @vl_cod_doc
			
			open c_ingreso_pago_factura 
			fetch c_ingreso_pago_factura into @vl_cod_ingreso_pago_factura, @vl_cod_ingreso_pago, @vl_monto_asignado
			WHILE @@FETCH_STATUS = 0 BEGIN								
				-- cursor en monto_doc_asignado
				declare c_monto_doc_asignado cursor for 
				select cod_monto_doc_asignado
					,monto_doc_asignado
					,cod_doc_ingreso_pago
				from monto_doc_asignado
				where cod_ingreso_pago_factura = @vl_cod_ingreso_pago_factura

				insert into ingreso_pago_factura (cod_ingreso_pago, monto_asignado, tipo_doc, cod_doc)
				values (@vl_cod_ingreso_pago, 0, 'FACTURA', @ve_cod_factura)
				set @vl_new_ingreso_pago_factura = @@identity

				open c_monto_doc_asignado 
				fetch c_monto_doc_asignado into @vl_cod_monto_doc_asignado, @vl_monto_doc_asignado, @vl_cod_doc_ingreso_pago
				WHILE (@@FETCH_STATUS = 0 and @vl_total_fa > 0)BEGIN	
					if(@vl_monto_doc_asignado >= @vl_total_fa) begin --se debe asignar solo lo necesario
						set @vl_por_asignar_fa = @vl_total_fa

						-- borrar monto_doc_asignado de la NV
					end
					else
						set @vl_por_asignar_fa = @vl_monto_doc_asignado

					-- monto_doc_asignado nuevo
					insert into monto_doc_asignado (cod_doc_ingreso_pago, cod_ingreso_pago_factura, monto_doc_asignado)
					values (@vl_cod_doc_ingreso_pago, @vl_new_ingreso_pago_factura, @vl_por_asignar_fa)
					-- actualiza monto_doc_asignado antiguo
					update monto_doc_asignado
					set monto_doc_asignado = monto_doc_asignado - @vl_por_asignar_fa
					where cod_monto_doc_asignado = @vl_cod_monto_doc_asignado
					-- rebaja el total por pagar
					set @vl_total_fa = @vl_total_fa - @vl_por_asignar_fa
					-- borra si monto = 0
					--delete monto_doc_asignado
					--where cod_monto_doc_asignado = @vl_cod_monto_doc_asignado
					  --and monto_doc_asignado = 0

					-- ingreso_pago_factura nuevo
					update ingreso_pago_factura
					set monto_asignado = monto_asignado + @vl_por_asignar_fa
					where cod_ingreso_pago_factura = @vl_new_ingreso_pago_factura
					-- actualiza ingreso_pago_factura antiguo
					update ingreso_pago_factura
					set monto_asignado = monto_asignado - @vl_por_asignar_fa
					where cod_ingreso_pago_factura = @vl_cod_ingreso_pago_factura
			
					fetch c_monto_doc_asignado into @vl_cod_monto_doc_asignado, @vl_monto_doc_asignado, @vl_cod_doc_ingreso_pago
				end
				close c_monto_doc_asignado
				deallocate c_monto_doc_asignado
					
				-- borra las tablas intermedias con monto asignado en cero
				delete monto_doc_asignado
				where monto_doc_asignado = 0

				delete ingreso_pago_factura
				where monto_asignado = 0

				fetch c_ingreso_pago_factura into @vl_cod_ingreso_pago_factura, @vl_cod_ingreso_pago, @vl_monto_asignado
			end
			close c_ingreso_pago_factura
			deallocate c_ingreso_pago_factura
		end --if (@vl_cod_doc is not null)
end
