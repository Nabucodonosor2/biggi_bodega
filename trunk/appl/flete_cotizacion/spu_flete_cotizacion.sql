------------------  spu_flete_cotizacion  ----------------------------
CREATE PROCEDURE [dbo].[spu_flete_cotizacion](@ve_operacion varchar(20), @ve_cod_flete_cotizacion numeric,@ve_nom_flete_cotizacion varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into flete_cotizacion (nom_flete_cotizacion,orden)
		values (@ve_nom_flete_cotizacion, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update	flete_cotizacion
		set		nom_flete_cotizacion	= @ve_nom_flete_cotizacion,
				orden					= @ve_orden
		where	cod_flete_cotizacion	= @ve_cod_flete_cotizacion;
	end 
	else if (@ve_operacion='DELETE') begin
		delete flete_cotizacion 
	    where cod_flete_cotizacion = @ve_cod_flete_cotizacion
	end 
	
	EXECUTE sp_orden_parametricas 'FLETE_COTIZACION'
END
go