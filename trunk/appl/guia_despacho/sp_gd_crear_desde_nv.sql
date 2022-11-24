alter PROCEDURE [dbo].[sp_gd_crear_desde_nv] (@ve_cod_nota_venta numeric, @ve_cod_usuario numeric)
AS
BEGIN  

		declare @K_PARAM_MAX_IT_GD			numeric,
				@vl_valor_max_cant_it_gd	varchar(100),
				@vl_cod_guia_despacho		numeric,
				@vl_cod_item_nota_venta		numeric,
				@vl_i						numeric,
				@vl_orden					numeric,
				@vl_item					varchar(10),
				@vl_cod_producto			varchar(30),
				@vl_nom_producto			varchar(100),
				@vl_cantidad				T_CANTIDAD,
				@vl_precio					T_PRECIO,
				@vl_cod_item_doc			numeric				

		set @K_PARAM_MAX_IT_GD = 28
		
		set @vl_valor_max_cant_it_gd = dbo.f_get_parametro(@K_PARAM_MAX_IT_GD)
		
		execute sp_gd_crear @ve_cod_nota_venta, @ve_cod_usuario	
		set @vl_cod_guia_despacho = @@identity

		declare c_cursor cursor for 
		select cod_item_nota_venta
		from item_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			dbo.f_nv_cant_por_despachar (cod_item_nota_venta, 'TODO_ESTADO') > 0
		
		open c_cursor 
		fetch c_cursor into @vl_cod_item_nota_venta

		set @vl_i = 1
		while @@fetch_status = 0 
		begin
			if (@vl_i > @vl_valor_max_cant_it_gd)
			begin
				execute sp_gd_crear @ve_cod_nota_venta, @ve_cod_usuario
				set @vl_cod_guia_despacho = @@identity
				set @vl_i = 1
			end		
			
			select @vl_orden = orden
			    ,@vl_item = item
			    ,@vl_cod_producto = cod_producto
			    ,@vl_nom_producto = nom_producto
			    ,@vl_cantidad = dbo.f_nv_cant_por_despachar(cod_item_nota_venta, 'TODO_ESTADO')
			    ,@vl_precio = precio
				,@vl_cod_item_doc = cod_item_nota_venta
			from item_nota_venta
			where cod_item_nota_venta = @vl_cod_item_nota_venta


			insert into item_guia_despacho(
				cod_guia_despacho,
				orden,
				item,
				cod_producto,
				nom_producto,
				cantidad,
				precio,
				cod_item_doc,
				tipo_doc)
			values
				(@vl_cod_guia_despacho,
				@vl_orden,
				@vl_item,
				@vl_cod_producto,
				@vl_nom_producto,
				@vl_cantidad,
				@vl_precio,
				@vl_cod_item_doc,
				'ITEM_NOTA_VENTA') 

			set @vl_i = @vl_i + 1	
			fetch c_cursor into @vl_cod_item_nota_venta
		end
		close c_cursor
		deallocate c_cursor

END
go