------------------f_get_tabs_por_modulo----------------
-- Esta función retorna un string con todos los nombre de los tab y su valor (si es accesible)

CREATE FUNCTION [dbo].[f_get_tabs_por_modulo](@ve_cod_item_menu varchar(10), @ve_cod_perfil numeric)
RETURNS varchar(500)
AS
BEGIN
	declare @res varchar(500),
			@cod_item_menu varchar(10),
			@nom_item_menu varchar (100),
			@autoriza_menu varchar(1)		


	declare c_cursor cursor for 
	
	select i.cod_item_menu, i.nom_item_menu, a.autoriza_menu from item_menu i, autoriza_menu a
	where i.cod_item_menu like @ve_cod_item_menu + '%'
			and i.cod_item_menu <> @ve_cod_item_menu and
			len(i.cod_item_menu) = len(@ve_cod_item_menu) + 2 and 
			a.cod_item_menu = i.cod_item_menu and
			a.cod_perfil = @ve_cod_perfil and
			i.TIPO_ITEM_MENU = 'T' and
			i.visible <> 'N'

	open c_cursor 
	fetch c_cursor into @cod_item_menu, @nom_item_menu, @autoriza_menu

	set @res = ''
	while @@fetch_status = 0 
	begin
		set @res = @res + @cod_item_menu + '|' + @nom_item_menu + '|' + @autoriza_menu + '|'
		fetch c_cursor into @cod_item_menu, @nom_item_menu, @autoriza_menu
	end
	close c_cursor
	deallocate c_cursor

	if (@res<>'')
		set @res = substring(@res, 1, len(@res)-1)
	return @res;
END
go
