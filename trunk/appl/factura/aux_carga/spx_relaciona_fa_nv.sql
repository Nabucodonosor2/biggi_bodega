CREATE PROCEDURE [dbo].[spx_relaciona_fa_nv]
AS
BEGIN  
	declare
		@cod_nota_venta		numeric
		,@cod_producto		varchar(100)
		,@count				numeric
		,@cod_item_factura	numeric
		,@cod_item_nota_venta		numeric
		,@cantidad		T_CANTIDAD

	declare c_item_fa cursor for 
	select f.cod_doc
			,i.cod_producto
			,i.cod_item_factura
			,i.cantidad
	from item_factura i, factura f
	where i.tipo_doc = '4D'
	  and f.cod_factura = i.cod_factura
	  and f.tipo_doc = 'NOTA_VENTA'

	open c_item_fa
	fetch c_item_fa into @cod_nota_venta,@cod_producto,@cod_item_factura, @cantidad
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

			update item_factura
			set tipo_doc = 'ITEM_NOTA_VENTA'
				,cod_item_doc = @cod_item_nota_venta
				,motivo_te='x'
			where cod_item_factura = @cod_item_factura
		end
		else begin
			set @cod_item_nota_venta = null
			select top 1  @cod_item_nota_venta=cod_item_nota_venta
			from item_nota_venta
			where cod_nota_venta = @cod_nota_venta
			  and cod_producto = @cod_producto
			  and dbo.f_nv_cant_por_facturar(cod_item_nota_venta, default) = @cantidad

			if (@cod_item_nota_venta is not null)
				update item_factura
				set tipo_doc = 'ITEM_NOTA_VENTA'
					,cod_item_doc = @cod_item_nota_venta
					,motivo_te='xx'
				where cod_item_factura = @cod_item_factura
			else begin
				select top 1  @cod_item_nota_venta=cod_item_nota_venta
				from item_nota_venta
				where cod_nota_venta = @cod_nota_venta
				  and cod_producto = @cod_producto
				  and dbo.f_nv_cant_por_facturar(cod_item_nota_venta, default) > @cantidad

				if (@cod_item_nota_venta is not null)
					update item_factura
					set tipo_doc = 'ITEM_NOTA_VENTA'
						,cod_item_doc = @cod_item_nota_venta
						,motivo_te='xx'
					where cod_item_factura = @cod_item_factura
			end 
		end 

		fetch c_item_fa into @cod_nota_venta,@cod_producto,@cod_item_factura, @cantidad
	end
	close c_item_fa
	deallocate c_item_fa
END
go