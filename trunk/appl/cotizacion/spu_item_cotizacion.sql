-------------------- spu_item_cotizacion ---------------------------------
CREATE PROCEDURE [dbo].[spu_item_cotizacion](
		@ve_operacion varchar(20),
		@ve_cod_item_cotizacion numeric,
		@ve_cod_cotizacion numeric=NULL, 
		@ve_orden numeric=NULL,
		@ve_item varchar(10)=NULL, 
		@ve_cod_producto varchar(100)=NULL, 
		@ve_nom_producto varchar(100)=NULL, 
		@ve_cantidad T_CANTIDAD=NULL, 
		@ve_precio T_PRECIO=NULL, 
		@ve_motivo_mod_precio varchar(100)=NULL, 
		@ve_cod_usuario_mod_precio numeric=NULL,
		@ve_tipo_te varchar(100)=NULL,
		@ve_motivo_te varchar(100)=NULL)
AS
	declare @precio_old numeric,
			@cod_item_cotizacion numeric
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into item_cotizacion(
					cod_cotizacion,
					orden,
					item,
					cod_producto,
					nom_producto,
					cantidad,
					precio,
					cod_tipo_te,		
					motivo_te)
		values		(
					@ve_cod_cotizacion,
					@ve_orden,
					@ve_item,
					@ve_cod_producto,
					@ve_nom_producto,
					@ve_cantidad,
					@ve_precio,
					@ve_tipo_te,
					@ve_motivo_te
					) 
	
		set @cod_item_cotizacion = @@identity
		if(@ve_motivo_mod_precio<>'') -- tiene motivo, por lo tanto se modificó el precio
		begin
			select @precio_old = precio_venta_publico
			from producto
			where cod_producto = @ve_cod_producto	

			insert into modifica_precio_cotizacion 
			values (@cod_item_cotizacion, @ve_cod_usuario_mod_precio, getdate(), @precio_old, @ve_precio, @ve_motivo_mod_precio)	
		end
	end 
	else if (@ve_operacion='UPDATE') begin
		select @precio_old = precio
		from item_cotizacion
		where cod_item_cotizacion = @ve_cod_item_cotizacion
	
		if(@ve_motivo_mod_precio<>'') -- tiene motivo, por lo tanto se modificó el precio
			insert into modifica_precio_cotizacion 
			values (@ve_cod_item_cotizacion, @ve_cod_usuario_mod_precio, getdate(), @precio_old, @ve_precio, @ve_motivo_mod_precio)
		
		update item_cotizacion
		set cod_cotizacion		=	@ve_cod_cotizacion,
			orden				=	@ve_orden,
			item				=	@ve_item,
			cod_producto		=	@ve_cod_producto,
			nom_producto		=	@ve_nom_producto,
			cantidad			=	@ve_cantidad,
			precio				=	@ve_precio,
			cod_tipo_te			=	@ve_tipo_te,				
			motivo_te			=	@ve_motivo_te   
		where cod_item_cotizacion	=	@ve_cod_item_cotizacion
	end
	else if (@ve_operacion='DELETE') begin
		delete modifica_precio_cotizacion
		where cod_item_cotizacion = @ve_cod_item_cotizacion
	
		delete  item_cotizacion 
	    where cod_item_cotizacion = @ve_cod_item_cotizacion
	end	
END
go
