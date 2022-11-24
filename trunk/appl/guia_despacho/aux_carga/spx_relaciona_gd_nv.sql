CREATE PROCEDURE [dbo].[spx_relaciona_gd_nv]
AS
BEGIN  
	declare
		@cod_nota_venta		numeric
		,@cod_producto		varchar(100)
		,@count				numeric
		,@cod_item_guia_despacho	numeric
		,@cod_item_nota_venta		numeric
		,@cantidad		T_CANTIDAD

	declare c_item_gd cursor for 
	select gd.cod_doc
			,i.cod_producto
			,i.cod_item_guia_despacho
			,i.cantidad
	from item_guia_despacho i, guia_despacho gd
	where i.tipo_doc = '4D'
	  and gd.cod_guia_despacho = i.cod_guia_despacho
	  and gd.cod_tipo_guia_despacho = 1

	open c_item_gd 
	fetch c_item_gd into @cod_nota_venta,@cod_producto,@cod_item_guia_despacho, @cantidad
	while @@fetch_status = 0 begin
		select @count=count(*) 
		from item_nota_venta
		where cod_nota_venta = @cod_nota_venta
		  and cod_producto = @cod_producto

		if (@count=1) begin
			select @cod_item_nota_venta=cod_item_nota_venta
			from item_nota_venta
			where cod_nota_venta = @cod_nota_venta
			  and cod_producto = @cod_producto

			update item_guia_despacho
			set tipo_doc = 'ITEM_NOTA_VENTA'
				,cod_item_doc = @cod_item_nota_venta
				,motivo_te='x'
			where cod_item_guia_despacho = @cod_item_guia_despacho
		end
		else begin
			set @cod_item_nota_venta = null
			select top 1  @cod_item_nota_venta=cod_item_nota_venta
			from item_nota_venta
			where cod_nota_venta = @cod_nota_venta
			  and cod_producto = @cod_producto
			  and dbo.f_nv_cant_por_despachar(cod_item_nota_venta, default) = @cantidad

			if (@cod_item_nota_venta is not null)
				update item_guia_despacho
				set tipo_doc = 'ITEM_NOTA_VENTA'
					,cod_item_doc = @cod_item_nota_venta
					,motivo_te='xx'
				where cod_item_guia_despacho = @cod_item_guia_despacho
			else begin
				select top 1  @cod_item_nota_venta=cod_item_nota_venta
				from item_nota_venta
				where cod_nota_venta = @cod_nota_venta
				  and cod_producto = @cod_producto
				  and dbo.f_nv_cant_por_despachar(cod_item_nota_venta, default) > @cantidad

				if (@cod_item_nota_venta is not null)
					update item_guia_despacho
					set tipo_doc = 'ITEM_NOTA_VENTA'
						,cod_item_doc = @cod_item_nota_venta
						,motivo_te='xx'
					where cod_item_guia_despacho = @cod_item_guia_despacho
			end 
		end 

		fetch c_item_gd into @cod_nota_venta,@cod_producto,@cod_item_guia_despacho, @cantidad
	end
	close c_item_gd
	deallocate c_item_gd
END
go