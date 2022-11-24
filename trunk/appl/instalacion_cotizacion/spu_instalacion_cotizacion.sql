------------------  spu_instalacion_cotizacion  ----------------------------
CREATE PROCEDURE [dbo].[spu_instalacion_cotizacion](@ve_operacion varchar(20), @ve_cod_instalacion_cotizacion numeric,@ve_nom_instalacion_cotizacion varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
if (@ve_operacion='INSERT') begin
		insert into instalacion_cotizacion (nom_instalacion_cotizacion,orden)
		values (@ve_nom_instalacion_cotizacion, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update	instalacion_cotizacion
		set		nom_instalacion_cotizacion	= @ve_nom_instalacion_cotizacion,
				orden					= @ve_orden
		where	cod_instalacion_cotizacion	= @ve_cod_instalacion_cotizacion;
	end
	else if (@ve_operacion='DELETE') begin
		delete instalacion_cotizacion 
    	where cod_instalacion_cotizacion = @ve_cod_instalacion_cotizacion
	end
	
	EXECUTE sp_orden_parametricas 'INSTALACION_COTIZACION'
END
go