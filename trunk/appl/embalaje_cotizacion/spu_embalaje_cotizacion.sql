------------------  spu_embalaje_cotizacion  ----------------------------
CREATE PROCEDURE [dbo].[spu_embalaje_cotizacion](@ve_operacion varchar(20),@ve_cod_embalaje_cotizacion numeric,@ve_nom_embalaje_cotizacion varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into embalaje_cotizacion (nom_embalaje_cotizacion,orden)
		values (@ve_nom_embalaje_cotizacion, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update	embalaje_cotizacion
		set		nom_embalaje_cotizacion	= @ve_nom_embalaje_cotizacion,
				orden					= @ve_orden
		where	cod_embalaje_cotizacion	= @ve_cod_embalaje_cotizacion;
	end
	else if (@ve_operacion='DELETE') begin
		delete embalaje_cotizacion 
    	where cod_embalaje_cotizacion = @ve_cod_embalaje_cotizacion	
	end

	EXECUTE sp_orden_parametricas 'EMBALAJE_COTIZACION'
END
go