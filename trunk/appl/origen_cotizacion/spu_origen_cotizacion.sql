------------------  spu_origen_cotizacion  --------------------------
CREATE PROCEDURE [dbo].[spu_origen_cotizacion](@ve_operacion varchar(20),@ve_cod_origen_cotizacion numeric,@ve_nom_origen_cotizacion varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into origen_cotizacion (nom_origen_cotizacion,orden)
		values (@ve_nom_origen_cotizacion, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update	origen_cotizacion
		set		nom_origen_cotizacion	= @ve_nom_origen_cotizacion,
				orden					= @ve_orden
		where	cod_origen_cotizacion	= @ve_cod_origen_cotizacion;
	end
	else if (@ve_operacion='DELETE') begin
		delete origen_cotizacion 
    	where cod_origen_cotizacion = @ve_cod_origen_cotizacion
	end
	
	EXECUTE sp_orden_parametricas 'ORIGEN_COTIZACION'
END
go