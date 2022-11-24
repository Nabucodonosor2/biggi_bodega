------------------spu_grupo_usuario----------------
CREATE PROCEDURE [dbo].[spu_grupo_usuario](@ve_operacion varchar(20),@ve_cod_grupo_usuario numeric,@ve_cod_grupo numeric=NULL, @ve_cod_usuario numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into grupo_usuario (cod_grupo, cod_usuario)
		values (@ve_cod_grupo, @ve_cod_usuario)
	end 
	if (@ve_operacion='UPDATE') begin
		update grupo_usuario 
		set cod_usuario = @ve_cod_usuario
	    where cod_grupo_usuario = @ve_cod_grupo_usuario
	end
	else if (@ve_operacion='DELETE') begin
		delete grupo_usuario 
    	where cod_grupo_usuario = @ve_cod_grupo_usuario
	end
	
END
go