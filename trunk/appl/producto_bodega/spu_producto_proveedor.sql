-------------------- spu_producto_proveedor ---------------------------------
CREATE PROCEDURE dbo.spu_producto_proveedor(@ve_operacion varchar(20)
											,@ve_cod_producto_proveedor numeric
											,@ve_cod_empresa numeric=NULL
											,@ve_cod_producto varchar(30)=NULL
											,@ve_cod_interno_producto varchar(30)=NULL
											,@ve_precio T_PRECIO=NULL
											,@ve_orden numeric=NULL
											,@ve_cod_usuario numeric=NULL)

AS
BEGIN
	declare	@count 						numeric,
			@cod_producto_proveedor		numeric

	if (@ve_operacion='INSERT') begin
		select @count = count(*) 
		from PRODUCTO_PROVEEDOR 
		where COD_EMPRESA = @ve_cod_empresa
			and COD_PRODUCTO = @ve_cod_producto

		if (@count > 0) begin
			update PRODUCTO_PROVEEDOR
			set ELIMINADO = 'N',
				FECHA_ELIMINADO = NULL,
				COD_USUARIO_ELIMINADO = null
			where COD_EMPRESA = @ve_cod_empresa
				and COD_PRODUCTO = @ve_cod_producto

			select @ve_cod_producto_proveedor = cod_producto_proveedor
			from PRODUCTO_PROVEEDOR
			where COD_EMPRESA = @ve_cod_empresa
				and COD_PRODUCTO = @ve_cod_producto

			set @ve_operacion='UPDATE'
		end 
		else begin
			insert into PRODUCTO_PROVEEDOR
				(COD_PRODUCTO, COD_INTERNO_PRODUCTO, COD_EMPRESA, ORDEN, ELIMINADO)
			values
				(@ve_cod_producto, @ve_cod_interno_producto, @ve_cod_empresa, @ve_orden, 'N')

			set @cod_producto_proveedor = @@identity

			insert into COSTO_PRODUCTO
				(COD_PRODUCTO_PROVEEDOR, FECHA_INICIO_VIGENCIA, PRECIO, COD_USUARIO)
			values
				(@cod_producto_proveedor, getdate(), @ve_precio, @ve_cod_usuario)
		end 
	end 
	if (@ve_operacion='UPDATE') begin
		UPDATE	PRODUCTO_PROVEEDOR
		SET		COD_PRODUCTO			= @ve_cod_producto
	           ,COD_INTERNO_PRODUCTO	= @ve_cod_interno_producto
	           ,COD_EMPRESA				= @ve_cod_empresa
	           ,ORDEN					= @ve_orden
	     WHERE	COD_PRODUCTO_PROVEEDOR	= @ve_cod_producto_proveedor

		insert into COSTO_PRODUCTO
			(COD_PRODUCTO_PROVEEDOR, FECHA_INICIO_VIGENCIA, PRECIO, COD_USUARIO)
		values
			(@ve_cod_producto_proveedor, getdate(), @ve_precio, @ve_cod_usuario)
	end
	else if (@ve_operacion='DELETE') begin
		UPDATE	PRODUCTO_PROVEEDOR
		SET		ELIMINADO = 'S'
	           ,FECHA_ELIMINADO	= getdate()
	           ,COD_USUARIO_ELIMINADO = @ve_cod_usuario
	     WHERE	COD_PRODUCTO_PROVEEDOR	= @ve_cod_producto_proveedor
	end	
END
go
