------------------  spu_perfil  --------------------------
CREATE PROCEDURE [dbo].[spu_perfil](@ve_operacion varchar(20),@ve_cod_perfil numeric,@ve_nom_perfil varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into perfil (nom_perfil)
		values (@ve_nom_perfil)
	end 
	if (@ve_operacion='UPDATE') begin
		update	perfil
		set		nom_perfil	= @ve_nom_perfil
		where	cod_perfil	= @ve_cod_perfil;
	end
	else if (@ve_operacion='DELETE') begin
		delete autoriza_menu
	    where cod_perfil = @ve_cod_perfil
	
		delete perfil 
	    where cod_perfil = @ve_cod_perfil
	end
END
go