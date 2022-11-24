-------------------- spu_persona ---------------------------------
CREATE PROCEDURE [dbo].[spu_persona](@ve_operacion varchar(20),@ve_cod_persona numeric, @ve_cod_sucursal numeric=NULL, @ve_nom_persona varchar(100)=NULL, @ve_cod_cargo numeric=NULL, @ve_telefono varchar(100)=NULL, @ve_fax varchar(100)=NULL, @ve_email varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into persona (cod_sucursal, nom_persona, cod_cargo, telefono, fax, email)
			values (@ve_cod_sucursal, @ve_nom_persona, @ve_cod_cargo, @ve_telefono, @ve_fax, @ve_email)
		end
	else if (@ve_operacion='UPDATE')
		begin
			update persona
			set cod_sucursal = @ve_cod_sucursal,
				nom_persona = @ve_nom_persona,
				cod_cargo = @ve_cod_cargo,
				telefono = @ve_telefono,
				fax = @ve_fax,
				email = @ve_email
			where cod_persona = @ve_cod_persona
		end 
	else if (@ve_operacion='DELETE')
		begin
			delete persona 
    		where cod_persona = @ve_cod_persona
		end 
END
go