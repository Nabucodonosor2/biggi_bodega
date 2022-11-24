CREATE PROCEDURE [dbo].[sp_nc_crear_desde_fa]
(
	@ve_cod_factura numeric, 
	@ve_cod_usuario numeric
)
AS
BEGIN  

		declare @vl_cod_max_cant_it_nc numeric,
			@vl_valor_max_cant_it_nc varchar(100),
			@vl_cod_nc  numeric,
			@vl_cod_item_factura numeric,
			@vl_i numeric,
			@vl_orden numeric,
			@vl_item varchar(10),
			@vl_cod_producto varchar(30),
			@vl_nom_producto varchar(100),
			@vl_cantidad T_CANTIDAD,
			@vl_precio numeric,
			@vl_cod_item_doc numeric 				

		set @vl_cod_max_cant_it_nc = 40
		
		select @vl_valor_max_cant_it_nc = valor
		from parametro
		where cod_parametro = @vl_cod_max_cant_it_nc
		
		execute sp_nc_crear @ve_cod_factura, @ve_cod_usuario	
		set @vl_cod_nc = @@identity
		
		declare c_cursor cursor for 
		select cod_item_factura
		from item_factura
		where cod_factura = @ve_cod_factura and
			dbo.f_fa_cant_por_nc (cod_item_factura, 'TODO_ESTADO') > 0
		
		open c_cursor 
		fetch c_cursor into @vl_cod_item_factura

		set @vl_i = 1
		while @@fetch_status = 0 
		begin
			if (@vl_i > @vl_valor_max_cant_it_nc)
			begin
				execute sp_nc_crear @ve_cod_factura, @ve_cod_usuario
				set @vl_cod_nc = @@identity
				set @vl_i = 1
			end		
			
			
			select @vl_orden = orden
			    ,@vl_item = item
			    ,@vl_cod_producto = cod_producto
			    ,@vl_nom_producto = nom_producto
			    ,@vl_cantidad = dbo.f_fa_cant_por_nc(cod_item_factura, 'TODO_ESTADO')
			    ,@vl_precio = precio
				,@vl_cod_item_doc = cod_item_factura
			from item_factura
			where cod_item_factura = @vl_cod_item_factura

			insert into item_nota_credito
			(
				COD_NOTA_CREDITO, 
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
				@vl_cod_nc,
				@vl_orden,
				@vl_item,
				@vl_cod_producto,
				@vl_nom_producto,
				@vl_cantidad,
				@vl_precio,
				@vl_cod_item_doc,
				'ITEM_FACTURA') 

			set @vl_i = @vl_i + 1	
			fetch c_cursor into @vl_cod_item_factura
		end
		close c_cursor
		deallocate c_cursor

		execute spu_nota_credito 'RECALCULA', @vl_cod_nc
END
go