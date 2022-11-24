-------------------- spu_accion_bitacora ---------------------------------
CREATE PROCEDURE [dbo].[spu_accion_bitacora](@ve_operacion varchar(20), @ve_cod_accion_bitacora numeric, @ve_nom_accion_bitacora varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
	insert into accion_bitacora (nom_accion_bitacora, orden)
	values (@ve_nom_accion_bitacora, @ve_orden)
	end
	if (@ve_operacion='UPDATE') begin
	update accion_bitacora 
	set nom_accion_bitacora = @ve_nom_accion_bitacora,
		orden = @ve_orden	
    where cod_accion_bitacora = @ve_cod_accion_bitacora
	end
	else if (@ve_operacion='DELETE') begin
		delete accion_bitacora 
    	where cod_accion_bitacora = @ve_cod_accion_bitacora
	end

	EXECUTE sp_orden_parametricas 'ACCION_BITACORA'
END
go