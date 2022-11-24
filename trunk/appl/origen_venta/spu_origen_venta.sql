------------------  spu_origen_venta  --------------------------
CREATE PROCEDURE [dbo].[spu_origen_venta](@ve_operacion varchar(20),@ve_cod_origen_venta numeric,@ve_nom_origen_venta varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into origen_venta (nom_origen_venta,orden)
		values (@ve_nom_origen_venta, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update	origen_venta
		set		nom_origen_venta	= @ve_nom_origen_venta,			
				orden					= @ve_orden
		where	cod_origen_venta	= @ve_cod_origen_venta;
	end
	else if (@ve_operacion='DELETE') begin
		delete origen_venta 
    	where cod_origen_venta = @ve_cod_origen_venta
	end
	
	EXECUTE sp_orden_parametricas 'origen_venta'
END
go