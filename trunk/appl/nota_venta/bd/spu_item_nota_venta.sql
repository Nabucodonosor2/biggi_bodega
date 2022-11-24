ALTER PROCEDURE [dbo].[spu_item_nota_venta](
						 @ve_operacion varchar(20)
						,@ve_cod_item_nota_venta numeric
						,@ve_cod_nota_venta numeric=NULL
						,@ve_orden numeric=NULL
						,@ve_item varchar(10)=NULL
						,@ve_cod_producto varchar(30)=NULL 
						,@ve_nom_producto varchar(100)=NULL
						,@ve_cantidad T_CANTIDAD=NULL
						,@ve_precio T_PRECIO=NULL
						,@ve_motivo_mod_precio varchar(100)=NULL
						,@ve_cod_usuario_mod_precio numeric=NULL
						,@ve_cod_tipo_gas numeric=NULL
						,@ve_cod_tipo_electricidad numeric=NULL
						,@ve_tipo_te numeric(10,2)=NULL
						,@ve_motivo_te varchar(100)=NULL
						,@ve_cod_usuario_elimina_item numeric (3) =NULL)
AS
declare @precio_old T_PRECIO,
	@k_tipo_te_otro numeric(10,2)
	,@vl_estado_nota_venta numeric
	,@kl_nv_confirmada numeric

set @k_tipo_te_otro = 7
set @kl_nv_confirmada = 4

BEGIN
	if (@ve_operacion='INSERT') begin
		-- en ocasiones el @ve_tipo_te es null, auque se ingreso este dato
		if (@ve_cod_producto = 'TE' and @ve_tipo_te is null)
			set @ve_tipo_te = @k_tipo_te_otro

		insert into item_nota_venta(
					cod_nota_venta,
					orden,
					item,
					cod_producto,
					nom_producto,
					cantidad,
					precio, 
					cod_tipo_gas, 
					cod_tipo_electricidad,
					cod_tipo_te,
					motivo_te)
		values		(
					@ve_cod_nota_venta,
					@ve_orden,
					@ve_item,
					@ve_cod_producto,
					@ve_nom_producto,
					@ve_cantidad,
					@ve_precio, 
					@ve_cod_tipo_gas,
					@ve_cod_tipo_electricidad,
					@ve_tipo_te,
					@ve_motivo_te)
		 	
		set @ve_cod_item_nota_venta = @@identity

	end 	
	else if (@ve_operacion='UPDATE') begin
		-- tiene motivo, por lo tanto se modificó el precio
		if(@ve_motivo_mod_precio is not null) 
		begin
			select @precio_old = precio
			from item_nota_venta
			where cod_item_nota_venta = @ve_cod_item_nota_venta
		
			if(@precio_old <> @ve_precio)
				insert into modifica_precio_nota_venta
				values (@ve_cod_item_nota_venta, @ve_cod_usuario_mod_precio, getdate(), @precio_old, @ve_precio, @ve_motivo_mod_precio)
		end	
		
		-- en ocasiones el @ve_tipo_te es null, auque se ingreso este dato
		if (@ve_cod_producto = 'TE' and @ve_tipo_te is null)
			set @ve_tipo_te = @k_tipo_te_otro

		update item_nota_venta
		set	cod_nota_venta = @ve_cod_nota_venta,
			orden = @ve_orden,
			item = @ve_item,
			cod_producto = @ve_cod_producto,
			nom_producto = @ve_nom_producto,
			cantidad = @ve_cantidad,
			precio = @ve_precio, 
			cod_tipo_gas = @ve_cod_tipo_gas, 
			cod_tipo_electricidad = @ve_cod_tipo_electricidad,
			cod_tipo_te			=	@ve_tipo_te,				
			motivo_te			=	@ve_motivo_te   
		where cod_item_nota_venta = @ve_cod_item_nota_venta
	end
	else if (@ve_operacion='DELETE') begin

		delete modifica_precio_orden_compra
		from modifica_precio_orden_compra mpoc, pre_orden_compra poc
		where mpoc.cod_pre_orden_compra = poc.cod_pre_orden_compra and
			poc.cod_item_nota_venta = @ve_cod_item_nota_venta

		delete pre_orden_compra
		where cod_item_nota_venta = @ve_cod_item_nota_venta

		delete modifica_precio_nota_venta
		where cod_item_nota_venta = @ve_cod_item_nota_venta

		delete autoriza_te
		where cod_item_nota_venta = @ve_cod_item_nota_venta
		
		select @vl_estado_nota_venta = cod_estado_nota_venta
		from nota_venta nv, item_nota_venta it 
		where nv.cod_nota_venta = it.cod_nota_venta
		and it.cod_item_nota_venta =  @ve_cod_item_nota_venta
		
		--si la NV esta confirmada crea registro = item_nota_venta_eliminada
		if (@vl_estado_nota_venta = @kl_nv_confirmada)
			insert into	item_nota_venta_eliminada
			(cod_item_nota_venta_eliminada
			,cod_nota_venta
			,orden
			,item
			,cod_producto
			,nom_producto
			,cantidad
			,precio
			,cod_tipo_gas
			,cod_tipo_electricidad
			,cod_tipo_te
			,motivo_te
			,cod_usuario
			,fecha
			,accion)
			select cod_item_nota_venta
				,cod_nota_venta
				,orden
				,item
				,cod_producto
				,nom_producto
				,cantidad
				,precio
				,cod_tipo_gas
				,cod_tipo_electricidad
				,cod_tipo_te
				,motivo_te
				,@ve_cod_usuario_elimina_item
				,getdate()
				,'DELETE'
			from item_nota_venta
			where cod_item_nota_venta = @ve_cod_item_nota_venta	
	
		delete ajuste_despachar_nota_venta
		where cod_item_nota_venta = @ve_cod_item_nota_venta

		delete item_nota_venta
		where cod_item_nota_venta = @ve_cod_item_nota_venta
	
	end
END
go