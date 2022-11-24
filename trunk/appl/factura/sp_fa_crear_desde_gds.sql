alter PROCEDURE [dbo].[sp_fa_crear_desde_gds]
(
	@ve_cod_nota_venta numeric,
	@ve_guias_despacho varchar(200), 
	@ve_cod_usuario numeric
)
AS
BEGIN  

		declare @K_PARAM_MAX_IT_FA numeric,
			@vl_valor_max_cant_it_fa varchar(100),
			@vl_cod_fa  numeric,
			@vl_cod_item_guia_despacho numeric,
			@vl_cod_guia_despacho numeric,
			@vl_i numeric,
			@vl_orden numeric,
			@vl_item varchar(10),
			@vl_cod_producto varchar(30),
			@vl_nom_producto varchar(100),
			@vl_cantidad T_CANTIDAD,
			@vl_precio T_PRECIO,
			@vl_count numeric,
		
			@vl_por_facturar T_CANTIDAD,
			@vl_fa_total_dscto1 T_PRECIO,
			@vl_fa_total_dscto2 T_PRECIO,
			@vl_fa_sum_dscto T_PRECIO,
			@k_cod_estado_anulado numeric,
			@vl_nv_monto_dscto1 T_PRECIO,
			@vl_nv_monto_dscto2 T_PRECIO,
			@vl_nv_sum_dscto T_PRECIO,
			@vl_dif_total_dscto T_PRECIO

		set @k_cod_estado_anulado = 4

		set @K_PARAM_MAX_IT_FA = 29	
		select @vl_valor_max_cant_it_fa = dbo.f_get_parametro(@K_PARAM_MAX_IT_FA)

		------------------------------------------------------------
		-- crea la FACTURA
		execute sp_fa_crearGd @ve_cod_nota_venta, @ve_cod_usuario	
		set @vl_cod_fa = @@identity
		------------------------------------------------------------
		
		declare c_cursor cursor for 
		select cod_item_guia_despacho, cod_guia_despacho
		from item_guia_despacho
		where cod_guia_despacho in (select * from f_split(@ve_guias_despacho, ' ')) and
			dbo.f_gd_cant_por_facturar (cod_item_guia_despacho, 'TODO_ESTADO') > 0
		order by cod_item_doc
		
		open c_cursor 
		fetch c_cursor into @vl_cod_item_guia_despacho, @vl_cod_guia_despacho

		set @vl_i = 1
		while @@fetch_status = 0 
		begin
			select @vl_orden = orden
			    ,@vl_item = item
			    ,@vl_cod_producto = cod_producto
			    ,@vl_nom_producto = nom_producto
			    ,@vl_cantidad = dbo.f_gd_cant_por_facturar (cod_item_guia_despacho, 'TODO_ESTADO')
				,@vl_precio = precio
			from item_guia_despacho
			where cod_item_guia_despacho = @vl_cod_item_guia_despacho

			if (@vl_precio > 0 and @vl_cantidad > 0)
			begin
				if (@vl_i > @vl_valor_max_cant_it_fa)
				begin
					execute spu_factura 'RECALCULA', @vl_cod_fa
					execute sp_fa_crearGd @ve_cod_nota_venta, @ve_cod_usuario
					set @vl_cod_fa = @@identity
					set @vl_i = 1
				end		
				insert into item_factura
				(
					COD_FACTURA, 
					ORDEN, 
					ITEM, 
					COD_PRODUCTO, 
					NOM_PRODUCTO, 
					CANTIDAD, 
					PRECIO, 
					COD_ITEM_DOC,
					TIPO_DOC
				)
				values(
					@vl_cod_fa,
					@vl_orden,
					@vl_item,
					@vl_cod_producto,
					@vl_nom_producto,
					@vl_cantidad,
					@vl_precio,
					@vl_cod_item_guia_despacho,
					'ITEM_GUIA_DESPACHO') 	
				set @vl_i = @vl_i + 1

				select @vl_count = count(*)
				from guia_despacho_factura
				where cod_guia_despacho = @vl_cod_guia_despacho
				  and cod_factura = @vl_cod_fa
				if (@vl_count=0)
					INSERT INTO GUIA_DESPACHO_FACTURA	VALUES (@vl_cod_guia_despacho,@vl_cod_fa )
			end
			fetch c_cursor into @vl_cod_item_guia_despacho, @vl_cod_guia_despacho
		end
		close c_cursor
		deallocate c_cursor

		execute spu_factura 'RECALCULA', @vl_cod_fa

		-- ajusta la ultima factura	
		select @vl_por_facturar = isnull(sum(dbo.f_nv_cant_por_facturar(it.cod_item_nota_venta, 'TODO_ESTADO')), 0)
		from item_nota_venta it, nota_venta nv
		where nv.cod_nota_venta = @ve_cod_nota_venta and
			nv.cod_nota_venta = it.cod_nota_venta
	
		if(@vl_por_facturar = 0)
		begin
			set @vl_fa_total_dscto1 = 0;
			set @vl_fa_total_dscto2 = 0;
			set @vl_fa_sum_dscto = 0;
			set @vl_nv_monto_dscto1 = 0;
			set @vl_nv_monto_dscto2 = 0;
			set @vl_nv_sum_dscto = 0;

			select @vl_fa_total_dscto1 = isnull(sum(monto_dscto1), 0)
					,@vl_fa_total_dscto2 = isnull(sum(monto_dscto2), 0)
			from factura
			where cod_tipo_factura = 1 -- venta
				and cod_doc = @ve_cod_nota_venta
				and cod_estado_doc_sii in (1, 2, 3)
				and cod_factura <> @vl_cod_fa
				
			-- el descuento de esta FA
			select @vl_fa_sum_dscto = monto_dscto1 + monto_dscto2
			from factura
			where cod_factura = @vl_cod_fa

			set @vl_fa_sum_dscto = @vl_fa_sum_dscto + @vl_fa_total_dscto1 + @vl_fa_total_dscto2
			
			--sumar todos los dsctos de NC asociadas a las FA
			declare
				@vl_nc_total_dscto1		numeric
				,@vl_nc_total_dscto2	numeric
			select @vl_nc_total_dscto1 = isnull(sum(monto_dscto1), 0)
					,@vl_nc_total_dscto2 = isnull(sum(monto_dscto2), 0)
			from nota_credito
			where cod_estado_doc_sii IN (2, 3)	-- IMPRESA O ENVIADA
			  and cod_doc in (SELECT COD_FACTURA
							  FROM FACTURA
							  WHERE COD_DOC = @ve_cod_nota_venta
								AND	COD_TIPO_FACTURA = 1	-- VENTA
								and	COD_ESTADO_DOC_SII in (2, 3)) -- IMPRESA O ENVIADA

			set @vl_fa_sum_dscto = @vl_fa_sum_dscto - @vl_nc_total_dscto1 - @vl_nc_total_dscto2

			-- obtiene dscto de NV
			select @vl_nv_monto_dscto1 = isnull(monto_dscto1, 0)
				,@vl_nv_monto_dscto2 = isnull(monto_dscto2, 0)
			from nota_venta 
			where cod_nota_venta = @ve_cod_nota_venta
		
			set @vl_nv_sum_dscto = @vl_nv_monto_dscto1 + @vl_nv_monto_dscto2

			set @vl_dif_total_dscto = @vl_nv_sum_dscto - @vl_fa_sum_dscto

			update factura
			set monto_dscto1 = monto_dscto1 + @vl_dif_total_dscto,
				ingreso_usuario_dscto1 = 'M'
			where cod_factura = @vl_cod_fa
			execute spu_factura'RECALCULA', @vl_cod_fa

		end -- if(@vl_por_facturar = 0)

END
go