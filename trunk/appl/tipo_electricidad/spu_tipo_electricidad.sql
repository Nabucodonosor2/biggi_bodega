------------------  spu_tipo_electricidad  --------------------------
CREATE PROCEDURE [dbo].[spu_tipo_electricidad](@ve_operacion varchar(20), @ve_cod_tipo_electricidad numeric,@ve_nom_tipo_electricidad varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into tipo_electricidad (nom_tipo_electricidad,orden)
		values (@ve_nom_tipo_electricidad, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update	tipo_electricidad
		set		nom_tipo_electricidad	= @ve_nom_tipo_electricidad,			
				orden					= @ve_orden
		where	cod_tipo_electricidad	= @ve_cod_tipo_electricidad
	end
	else if (@ve_operacion='DELETE') begin
		delete tipo_electricidad 
    	where cod_tipo_electricidad = @ve_cod_tipo_electricidad
	end

	EXECUTE sp_orden_parametricas 'TIPO_ELECTRICIDAD'
END
go