-------------------- spu_pais ---------------------------------
CREATE PROCEDURE [dbo].[spu_pais](@ve_operacion varchar(20),@ve_cod_pais numeric, @ve_nom_pais varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into pais (cod_pais, nom_pais)
		values (@ve_cod_pais, @ve_nom_pais)
	end 
	if (@ve_operacion='UPDATE') begin
		update pais 
		set cod_pais = @ve_cod_pais , nom_pais = @ve_nom_pais 
	    where cod_pais = @ve_cod_pais
	end
	else if (@ve_operacion='DELETE') begin
		delete pais 
    	where cod_pais = @ve_cod_pais
	end
	
END
go
