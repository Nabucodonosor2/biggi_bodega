-------------------- spu_familia_producto ---------------------------------
CREATE PROCEDURE [dbo].[spu_familia_producto](@ve_operacion varchar(20), @ve_cod_familia_producto numeric, @ve_nom_familia_producto varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into familia_producto (nom_familia_producto, orden)
		values (@ve_nom_familia_producto, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update familia_producto 
		set nom_familia_producto = @ve_nom_familia_producto, orden = @ve_orden
	    where cod_familia_producto = @ve_cod_familia_producto
	end 
	else if (@ve_operacion='DELETE') begin
		delete familia_producto 
    	where cod_familia_producto = @ve_cod_familia_producto
	end 
	
	EXECUTE sp_orden_parametricas 'FAMILIA_PRODUCTO'
END
go