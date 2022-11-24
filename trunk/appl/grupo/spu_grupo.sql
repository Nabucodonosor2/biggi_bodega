------------------spu_grupo----------------
CREATE PROCEDURE [dbo].[spu_grupo](@ve_operacion varchar(20),@ve_cod_grupo numeric, @ve_nom_grupo varchar(100)=NULL, @ve_cod_usuario numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into grupo (nom_grupo, cod_usuario)
		values (@ve_nom_grupo, @ve_cod_usuario)
	end 
	if (@ve_operacion='UPDATE') begin
		update grupo 
		set nom_grupo = @ve_nom_grupo, 
			cod_usuario = @ve_cod_usuario
	    where cod_grupo = @ve_cod_grupo
	end
	else if (@ve_operacion='DELETE') begin
		delete grupo 
    	where cod_grupo = @ve_cod_grupo
	end
	
END
go