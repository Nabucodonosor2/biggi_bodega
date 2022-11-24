-------------------- spu_tipo_te ---------------------------------
CREATE PROCEDURE [dbo].[spu_tipo_te](@ve_operacion varchar(20), @ve_cod_tipo_te numeric,@ve_nom_tipo_te varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into tipo_te (nom_tipo_te,orden)
		values (@ve_nom_tipo_te, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update	tipo_te
		set		nom_tipo_te	= @ve_nom_tipo_te,			
				orden						= @ve_orden
		where	cod_tipo_te	= @ve_cod_tipo_te;
	end
	else if (@ve_operacion='DELETE') begin
		delete tipo_te 
    	where cod_tipo_te = @ve_cod_tipo_te
	end
	
	EXECUTE sp_orden_parametricas 'TIPO_TE'
END
go