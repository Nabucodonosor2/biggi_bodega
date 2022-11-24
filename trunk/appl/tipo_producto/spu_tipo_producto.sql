------------------  spu_tipo_producto  --------------------------
CREATE PROCEDURE [dbo].[spu_tipo_producto](@ve_operacion varchar(20),@ve_cod_tipo_producto numeric, @ve_nom_tipo_producto varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into tipo_producto (nom_tipo_producto, orden)
		values (@ve_nom_tipo_producto, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update tipo_producto
		set nom_tipo_producto = @ve_nom_tipo_producto,
			orden = @ve_orden
		where cod_tipo_producto = @ve_cod_tipo_producto
	end
	else if (@ve_operacion='DELETE') begin
		delete tipo_producto 
    	where cod_tipo_producto = @ve_cod_tipo_producto
	end

	EXECUTE sp_orden_parametricas 'TIPO_PRODUCTO'
END
go